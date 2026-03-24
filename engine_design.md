# Engine Implementation Design: The Rust Actor Adapters

This document details how the individual `EngineActor` structures are built in Rust. Since Tess Snow orchestrates the execution, these internal engines are responsible for actually calling external APIs (OpenAI, Anthropic, Gemini, Grok/Ollama), loading distinct agent identities, and exposing specialized toolsets.

## 1. The Core Engine Trait

To allow Tess Snow to dispatch tasks without needing to know *how* an engine fulfills them, all engines must implement a common `ExecutionEngine` trait.

```rust
use async_trait::async_trait;

#[async_trait]
pub trait ExecutionEngine: Send + Sync {
    /// The unique identifier for this engine.
    fn id(&self) -> EngineId;

    /// The specific system identity prompt (CLAUDE.md / IDENTITY.md equivalent)
    fn get_system_prompt(&self) -> String;

    /// The actual execution pipeline. This wraps the specific API SDKs
    /// (e.g., reqwest for Claude, async-openai for Codex).
    async fn execute_task(&self, payload: RunPayload) -> Result<EngineResult, EngineError>;
    
    /// Some engines have specialized tool access (e.g. bash, fs).
    fn available_tools(&self) -> Vec<ToolDefinition>;
}
```

## 2. Engine Implementations

### A. The Custom Paired Unit: `MasonOllanEngine`

**Stack:** Grok Code (API / Planning) + Ollama (Local / Execution)
**Purpose:** Implementation and feature building.

This engine does not just call one API. It acts as a mini-orchestrator *inside* the Actor boundary.

```rust
pub struct MasonOllanEngine {
    grok_api_key: String,
    ollama_host: String, // e.g., http://localhost:11434
}

#[async_trait]
impl ExecutionEngine for MasonOllanEngine {
    fn id(&self) -> EngineId { EngineId::MasonOllan }
    
    fn get_system_prompt(&self) -> String {
        // Defines Mason's identity as a break-down architect
        "You are Mason, the primary builder. Your job is to take a feature spec and decompose it into 1-2 atomic, deterministic steps that a local junior developer (Ollan) can safely execute...".into()
    }

    async fn execute_task(&self, payload: RunPayload) -> Result<EngineResult, EngineError> {
        // Step 1: MASON (Grok Code) Phase
        // Call Grok API with the payload to get a structured `Vec<AtomicTask>` plan.
        let atomic_plan = self.call_grok_mapping_layer(payload).await?;

        // Step 2: OLLAN (Local Execution) Phase
        // Loop through the atomic tasks and use a fast local model via Ollama to execute.
        for atomic_step in atomic_plan {
             let ollan_prompt = format!("You are Ollan. Execute this single step: {}", atomic_step.instruction);
             self.call_ollama_local(ollan_prompt).await?;
        }
        
        Ok(EngineResult::success())
    }
}
```

### B. `SeraphineEngine`

**Stack:** Anthropic API (Claude Opus 4.5)
**Purpose:** Architecture, test suite design, complex reasoning.

```rust
pub struct SeraphineEngine {
    anthropic_api_key: String,
    anthropic_client: reqwest::Client,
}

#[async_trait]
impl ExecutionEngine for SeraphineEngine {
    fn id(&self) -> EngineId { EngineId::Seraphine }
    
    fn get_system_prompt(&self) -> String {
        "You are Seraphine, the Architect. You handle high-risk features and draft the underlying architecture, constraints, and test plans. You MUST format your output as a strict JSON PLAN...".into()
    }

    async fn execute_task(&self, payload: RunPayload) -> Result<EngineResult, EngineError> {
        let request_body = json!({
            "model": "claude-3-5-sonnet-20241022", // Switch to Opus 4.5 when available natively
            "system": self.get_system_prompt(),
            "messages": [{"role": "user", "content": payload.instructions}],
            "max_tokens": 8192,
        });
        
        // Execute the HTTP call using reqwest, tracking token usage directly
        // to pass back in the `EngineResult` so Tess Snow can calculate exhaustion.
        let response = self.call_anthropic_api(request_body).await?;
        
        Ok(EngineResult::from_anthropic(response))
    }
}
```

### C. `KaelenEngine`

**Stack:** Google Gemini API
**Purpose:** Research, pattern matching, external investigation.

```rust
pub struct KaelenEngine {
    gemini_api_key: String,
}

#[async_trait]
impl ExecutionEngine for KaelenEngine {
    fn id(&self) -> EngineId { EngineId::Kaelen }
    
    fn get_system_prompt(&self) -> String {
        "You are Kaelen, the Explorer. Your goal is to research technical problems, compare architectural patterns, and provide documented insights. You have access to Web Search tools...".into()
    }

    fn available_tools(&self) -> Vec<ToolDefinition> {
        // Kaelen uniquely gets the Google Search grounding tools natively
        vec![ToolDefinition::google_search()]
    }

    async fn execute_task(&self, payload: RunPayload) -> Result<EngineResult, EngineError> {
        // Utilize the Gemini API, enabling Google Search Grounding to ensure
        // Kaelen can actually fulfill the research mandate.
        let response = self.call_gemini_api_with_grounding(payload).await?;
        
        Ok(EngineResult::from_gemini(response))
    }
}
```

### D. `RowanEngine`

**Stack:** OpenAI API (GPT-5.3 Codex / or newest o-series reasoning models)
**Purpose:** Surgical multi-file refactors, consistency enforcement.

```rust
pub struct RowanEngine {
    openai_client: async_openai::Client<async_openai::config::OpenAIConfig>,
}

#[async_trait]
impl ExecutionEngine for RowanEngine {
    fn id(&self) -> EngineId { EngineId::Rowan }
    
    fn get_system_prompt(&self) -> String {
        "You are Rowan, the Mechanical Refactor Specialist. You perform surgical codebase-wide changes, enforcing consistency. Your edits must be returned as valid Unix diff patches or precise file replacement arrays...".into()
    }

    async fn execute_task(&self, payload: RunPayload) -> Result<EngineResult, EngineError> {
        // Creates a standardized ChatGPT completion request.
        // Rowan is heavily reliant on passing the raw project AST or multiple
        // file strings in the payload `RunPayload.repo_path`.
        let request = CreateChatCompletionRequestArgs::default()
            .model("gpt-4o") // Placeholder for 5.3 Codex
            .messages(build_messages(self.get_system_prompt(), payload))
            .build()?;

        let response = self.openai_client.chat().create(request).await?;
        
        // Parse the rate-limit headers from the OpenAI SDK to explicitly 
        // inform Tess Snow of Rowan's API Stress/Exhaustion.
        let status = EngineResult::from_openai(response);
        Ok(status)
    }
}
```

---

## 3. Handling Differing Exhaustion Metrics

Because these underlying models return different metrics (Anthropic returns detailed token structures, OpenAI has stringent RPM limit headers, Ollama has compute heat), the `execute_task` function maps the unique API responses down into the standard `EngineResult` format defined in `plan.md`.

For example, Rowan (`OpenAI`) parses `x-ratelimit-remaining-tokens`, whereas Seraphine (`Anthropic`) maps `response.usage.input_tokens` heavily against the total context window size to calculate `context_near_limit`.

Tess Snow doesn't care *how* Rowan got tired, she just receives an `EngineResult` with `rate_limited: true` and puts Rowan in a 30 minute timeout.
