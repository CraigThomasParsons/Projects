# 🧠 Personal Executive-Function Support Loop — Context

> Purpose: This document captures **what I am working on, why it matters, and how the system is supposed to help me**.\
> It is meant to reduce anxiety when switching tasks and make it easy to resume later.

---

## 🎯 High-Level Goal

Build a personal, offline-first, developer-friendly system that acts as:

- A **continuous capture system** for ideas and conversations
- A **project-based memory store** (no lost context)
- A **task triage and state snapshot tool**
- A "what was I doing?" recovery assistant

This system is designed to support how my brain actually works:

- I start things easily
- I struggle with switching between tasks
- I get anxious about not finishing
- I generate many parallel ideas
- I need strong external state, not more discipline

---

## 🧩 Core Problems This System Solves

1. **Task Switching Anxiety**\
   Switching feels like abandoning work because state is not reliably preserved.

2. **Loss of Context Over Time**\
   When I return to a project, I forget:

   - what I was doing
   - why I made certain decisions
   - what broke last

3. **Overwhelming Backlogs**\
   Writing things down without structure creates large, stressful to-do lists.

4. **Parallel Idea Explosion**\
   Many ideas feel equally important and interesting, leading to cognitive overload.

---

## ✅ Design Principles

### 1. Projects Are Containers

All work lives inside **project folders**, not in global task lists.

Each project should be resumable without searching elsewhere.

---

### 2. State Is More Important Than Tasks

Tasks alone are not enough. Each project must track:

- what was happening
- what is blocked
- what changed recently
- what the next small step is

---

### 3. Safe Suspension > Strict Prioritization

The system should support:

- pausing projects safely
- resuming with confidence
- never "losing" ideas

Instead of forcing hard choices, we manage:

- Active
- Paused
- Incubating
- Blocked

---

### 4. Chat Is a First-Class Input

Conversations with LLMs are:

- design discussions
- debugging sessions
- planning meetings

They must be:

- saved automatically
- linked to projects
- searchable later

---

## 🗂️ Proposed Project Folder Structure (Draft)

Each project lives in its own directory:

```
ProjectName/
  context.md        # why this project exists
  state.md          # current situation and blockers
  next_actions.md   # very small, concrete steps
  tasks.md          # backlog (not urgent)
  chat.log          # full conversation history
  snapshots/
    2026-01-15.md   # state snapshot
  logs/             # command output, errors, traces
```

---

## 🔁 Daily / Session Workflow (Target)

### When Starting Work

- Review `state.md`
- Check `next_actions.md`
- Resume from last snapshot

### While Working

- Chat and notes are auto-captured
- Important events appended to `state.md`

### When Stopping

- Write a short snapshot:
  - what I was doing
  - what worked
  - what broke
  - what I should do next

This makes future resumption low-stress.

---

## 🛠️ Planned System Components

### Phase 1 — Filesystem + Manual Process

- Folder structure
- Markdown templates
- Manual snapshots

### Phase 2 — Chat Capture Pipeline

- Websocket chat UI
- Auto-save transcripts
- Project tagging

### Phase 3 — Resume Engine

- Show last activity
- Suggest next action
- Surface recent errors/logs

### Phase 4 — Jira-like Project Manager

- Project dashboard
- Status lanes
- Time-based history

---

## 🧭 Long-Term Vision

This system should eventually act like:

- an external working memory
- a project continuity engine
- a gentle accountability partner

Not a pressure tool. Not a guilt machine.

The goal is to support momentum, not punish inconsistency.

---

## ✨ Why This Matters (Personal)

I build complex systems and long-running projects.

My biggest blocker is not skill or motivation — it is:

> keeping stable mental state across time

If I can solve that, I can finish far more of what I start.

This project is therefore not a side tool — it is foundational infrastructure for everything else I build.

---
