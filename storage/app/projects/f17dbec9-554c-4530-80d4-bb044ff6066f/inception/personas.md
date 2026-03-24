# Target Personas

## Piper / Agile Coach & Story Analyst
**Tech Level:** Medium (Focused on logic and requirements, not syntax)

### Goals
To clarify human intent, ask the right questions, and translate vague visions into structured Epics, Stories, and Tasks with explicit acceptance criteria.

### Frustrations
Ambiguity, lack of business goals, and developers who jump straight to coding without locking down the behavior (BDD) first.

### Context
Operating entirely within the ChatProjects UI (TheDevBacklog) during the "Shift Left" planning phase before any code is written.

---

## Mason / Deterministic Task Compiler
**Tech Level:** High

### Goals
 To ingest Piper's ChatProjects stories and quickly compile them into an immutable, strict JSON TaskPacket (v1.0 protocol) to hand off to the builders.

### Frustrations
 Incomplete specifications, changing requirements mid-task, and agents who try to exceed their boundaries or magically redefine scope.

### Context
Operating in the background mapping layer, taking explicit database tickets and turning them into secure filesystem execution commands.

---

## Ollan / Stateless Execution Worker
**Tech Level:** High

### Goals
To receive an immutable TaskPacket from Mason, execute it exactly once within a secure sandbox, and return a clean ArtifactBundle containing evidence of execution.

### Frustrations
Complex environments with unrestricted paths, loops that force self-reflection, and tasks that lack clear success criteria.

### Context
Running strictly on the local developer filesystem, detached from the broader application logic, respecting allowed/forbidden paths and generating raw diffs.

---

## Deckard / Git Historian & Repository Custodian
**Tech Level:** High

### Goals
 To monitor the pipeline and ensure that all successful ArtifactBundles are safely committed and pushed to feature branches as strict checkpoints.

### Frustrations
Uncommitted working directories, lost code, and developers (human or A.I.) working directly on the main/production branch without PRs.

### Context
Sitting at the intersection of the local filesystem execution (Ollan) and the version control system (GitHub), operating purely via Git commands.

---

## Craig, a Human
**Tech Level:** Okay level

### Goals
To define high-level product vision, review clean Pull Requests, and shepherd quality code into production without having to execute the repetitive boilerplate tasks.

### Frustrations
"Idea drift," where A.I. agents lose context or act like shape-shifters instead of specialized workers. Also frustrated by context overload when trying to use generalized LLMs for complex, multi-file software engineering.

### Context
 At the very beginning of the pipeline (to dictate intent) and at the very end of the GitHub TYS loop (to approve PRs to production).

---

