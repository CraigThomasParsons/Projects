# System Architecture: Rust Actor-Model Dispatcher

This document outlines the detailed architecture for an asynchronous, event-driven dispatcher system (Tess Snow) designed in Rust using the `tokio` asynchronous runtime and `mpsc` multi-producer, single-consumer channels.

## 1. Architectural Overview

The system relies on independent, concurrently running **Actors** communicating exclusively via message passing.

### Core Actors

- **TessSnowActor (Dispatcher/Coordinator):** Owns routing logic, engine assignment, and exhaustion checks. Subscribes to events and decides step-by-step assignments ("Assign Task X to Engine Y").
- **EngineActors:** Individual spawned actors representing the specialized agents (`MasonOllanEngine`, `RowanEngine`, `KaelenEngine`, `SeraphineEngine`). The `MasonOllanEngine` is a combined actor: Mason utilizes a powerful reasoning model (like Grok) to break down bugs or goals into atomic tasks, while Ollan utilizes a fast local LLM (like Ollama) to execute those atomic tasks sequentially. These act as wrappers calling external CLI/APIs.
- **VeraQaActor:** Handles independent verification.
- **TaskStoreActor:** The ultimate source of truth for task state transitions. Validates movement through the overarching workflow pipeline.
- **EventBusActor:** The centralized Pub-Sub distribution queue. All actors push events here, which are broadcasted to registered subscribers.
- **PersistenceActor:** Subscribes to the event bus and strictly handles fast appends/writes to disk (SQLite via `sqlx` or highly concurrent JSONL files).
- **TickerActor:** A simple timer loop that emits `Tick` events at intervals to drive exhaustion decay, TTL/timeout evaluations, and stuck-task monitoring.

---

## 2. Core Data Models

### Task State & Properties

Tasks are passed around with their full immutable context attached.

```rust
#[derive(Clone, Debug)]
pub struct Task {
    pub id: String,
    pub state: TaskState,
    pub task_type: TaskType,
    pub complexity: u8,
    pub risk: Risk,
    pub requires_repo: bool,
    pub requires_research: bool,
    pub assigned_engine: Option<EngineId>,
    pub retry_count: u8,
    pub last_update: std::time::SystemTime,
}

#[derive(Clone, Copy, Debug, PartialEq, Eq)]
pub enum TaskState {
    Backlog,
    Todo,
    InProgress,
    PeerReview,
    Review,
    Done,
    Blocked,
}
```

### Engine & Exhaustion State

Each engine represents a distinct execution unit with its own stress limits (e.g., API rate limits, context window limits, human exhaustion).

```rust
#[derive(Clone, Copy, Debug, PartialEq, Eq, Hash)]
pub enum EngineId { MasonOllan, Rowan, Kaelen, Seraphine }

#[derive(Clone, Debug)]
pub struct EngineState {
    pub id: EngineId,
    pub exhaustion: f32, // Decimal strictly clamped 0.0 - 1.0
    pub cooldown_until: Option<std::time::SystemTime>,
    pub fail_streak: u8,
    pub last_heartbeat: std::time::SystemTime,
}
```

---

## 3. Communication Protocol (Messages & Events)

All internal coordination is entirely message-driven via `mpsc` channels, preventing deadlocks or unwanted synchronization delays.

```rust
use tokio::sync::mpsc;

pub enum Msg {
    // API / External Input
    CreateTask(Task),
    EngineHeartbeat { engine: EngineId },

    // Core Dispatch Instructions
    DispatchNow { task_id: String },
    AssignTask { task_id: String, engine: EngineId },

    // Execution Commands
    RunTask { task_id: String, engine: EngineId, payload: RunPayload },
    EngineResult(EngineResult),

    // Quality Assurance
    ReadyForQA { task_id: String, verification: Vec<String>, commit_ref: String },
    QaResult(QaResult),

    // System Operations
    Tick,
    Publish(Event),
    Subscribe { subscriber: mpsc::Sender<Event> },
}
```

### Event Struct Definitions

Used for Pub/Sub subscriptions (like persistence to `sqlx` and dashboard UI websockets).

```rust
#[derive(Clone, Debug)]
pub enum Event {
    TaskCreated { task_id: String },
    TaskStateChanged { task_id: String, from: TaskState, to: TaskState },
    TaskAssigned { task_id: String, engine: EngineId },
    EngineExhaustionUpdated { engine: EngineId, exhaustion: f32, cooldown_until: Option<std::time::SystemTime> },
    EngineCompleted { task_id: String, engine: EngineId, status: EngineStatus },
    QaCompleted { task_id: String, pass: bool },
    TaskBlocked { task_id: String, reason: String },
}
```

---

## 4. Algorithmic Dispatch Logic (Tess Snow)

The dispatcher assigns tasks by dynamically calculating a "Fit Score" for all active, un-cooldown engines. The formula evaluates:

1. **Engine Specialization Match**
2. **Exhaustion Penalty**
3. **Context-Switching Penalty**

```rust
fn score(engine: EngineId, es: &EngineState, task: &Task) -> f32 {
    let fit = compute_fit(engine, task);                 // Base affinity (e.g. Mason on Med/High tasks is 0.85)
    let exhaustion_penalty = es.exhaustion * 0.8;        // Stress deduction
    let sw = switch_penalty(engine, task);               // Context-switch memory penalty
    
    fit - exhaustion_penalty - sw
}
```

### Penalty Execution Triggers (Event-Driven)

Engines gain stress based on the *result* payloads sent back via `EngineResult`.

- **Hard Rate Limit:** Exhaustion +0.35, forces a 30-minute Hard Cooldown.
- **Fail Streak:** Exhaustion compounding by `(fails * 0.08)`.
- **High Token Usage:** Exhaustion +0.15 (soft warning).

Exhaustion is linearly reduced periodically using the clock cycle ticks from `TickerActor` (`Tick` event).

---

## 5. Security & Verification (TaskStore Gatekeeper)

Tess Snow can suggest assignment and transitions, but the **TaskStoreActor** owns the explicit transition logic rules:

- `Todo -> InProgress`: Only valid if an Agent was explicitly assigned.
- `InProgress -> PeerReview`: Only valid if the Engine returned a `Success` message.
- `PeerReview -> Review`: Only valid if `VeraQaActor` returned a `pass` assertion.

Illegal transitions drop with logs, preventing broken logic flows.

---

## 6. Implementation Priorities

- [ ] Initialize standard `tokio` core infrastructure (`[tokio::main]`).
- [ ] Spin up `EventBusActor` and `mpsc` registry to handle initial pub/sub links.
- [ ] Implement `TaskStoreActor` state transitions to establish basic rules.
- [ ] Build `TessSnowActor` scoring logic (`compute_fit`, `score`, `decay_exhaustion`).
- [ ] Stub basic `EngineActor` shells and connect them to the EventBus.
- [ ] Bring up `PersistenceActor` utilizing `sqlx` to sink the event streams securely.
