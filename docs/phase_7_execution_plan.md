# Phase 7 — Full Pipeline Verification & Human‑in‑the‑Loop Execution

## Purpose

Phase 7 validates the **entire Project → Piper → Constructicon → ConstructionSite loop** under real operating conditions, with a human intentionally embedded in the decision points.

This phase does **not** introduce autonomy.
It proves correctness, traceability, and trust.

---

## Objectives

1. Validate all artifact contracts (`.req`, `.plan`, `.report`)
2. Exercise every Constructicon stage in sequence
3. Confirm Piper can reason over reports
4. Ensure Projects remain authoritative and untouched
5. Prove a human can pause, inspect, and resume safely

---

## Preconditions

- At least one real Project exists
- `.project/` metadata is populated
- Piper and Constructicon are installed but idle
- ConstructionSite is empty

---

## Phase 7.1 — Manual Job Declaration

**Human Action**

Say or type:

> "This is ready to go into the construction site"

**System Response**

Laravel / Projects layer:

- Freezes current `.project/` state
- Materializes a job bundle

```
Piper/inbox/<job_id>.req/
├── context.md
├── constraints.md
├── goals.md
└── tasks.md
```

No interpretation. No planning.

---

## Phase 7.2 — Piper Planning Pass

**Input**

- `<job_id>.req`

**Piper Responsibilities**

- Read artifacts
- Identify missing or conflicting information
- If incomplete → stop and report
- If complete → emit `.plan`

**Output**

```
Piper/outbox/<job_id>.plan
```

Human may inspect before proceeding.

---

## Phase 7.3 — Constructicon Execution

### Stage Order

1. Planner
2. Scaffolder
3. Generator
4. Builder
5. Tester

Each stage:

- Consumes previous artifact
- Writes outputs to ConstructionSite
- Emits a `.report`

Failure at any stage:

- Stops the pipeline
- Writes failure report

No retries.

---

## Phase 7.4 — Local Model Verification

**Generator Stage**

- Uses local LLM (e.g. Ollama)
- One file per prompt
- Deterministic temperature and seed

Artifacts must be written **only** inside ConstructionSite.

---

## Phase 7.5 — Testing & Validation

**Tester Stage Responsibilities**

- Syntax checks
- Minimal build commands
- Linting if available

All results are written to:

```
ConstructionSite/<ProjectName>/reports/
```

---

## Phase 7.6 — Human Review Gate

**Human Responsibilities**

- Inspect diffs
- Read `.report`
- Decide:
  - Accept
  - Reject
  - Revise constraints

No automatic merge.

---

## Phase 7.7 — Feedback Loop

**If Accepted**

- Human merges changes into Projects
- `.project/history.md` updated
- `state.json` updated

**If Rejected**

- New `.req` generated with revisions

---

## Success Criteria

- Every artifact exists and is readable
- No stage mutates Projects directly
- Pipeline halts cleanly on failure
- Human can resume from any phase

---

## Non‑Goals

- No background execution
- No self‑triggering agents
- No automatic retries
- No silent fixes

---

## Why Phase 7 Matters

This phase is the **trust‑building phase**.

After Phase 7:

- Autonomy is optional
- Manual mode remains sufficient
- The system is usable without AI

Phase 7 completion is a prerequisite for any future self‑improving behavior.

# The test

- I want to test the entire pipeline with a single command
- The first test should be a simple todo list application
- The second test should be a more complex application that uses multiple stages
- The third test should be a test of the feedback loop
