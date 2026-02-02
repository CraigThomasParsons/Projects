# Context — Personal Executive-Function Support Loop

## System

- Name: Personal Executive-Function Support Loop
- Scope: Offline-first, project-centered cognitive support system
- Current Phase: Phase 1 — Files, UI, and Human-in-the-Loop Validation

This system exists to preserve **mental state across time**, not to automate decisions.

---

## Primary Goal

Build a personal system that:

- captures conversations and ideas continuously
- organizes them by **project**
- preserves context when switching tasks
- makes resuming work low-anxiety and low-effort

The system must work even if:

- ChatGPT is unavailable
- automation is disabled
- only the filesystem remains

---

## Core Problems Being Solved

1. **Task Switching Anxiety**
   - Switching tasks feels like abandonment because state is lost.

2. **Context Loss Over Time**
   - On return, I forget:
     - what I was doing
     - why decisions were made
     - what broke last

3. **Overwhelming Backlogs**
   - Flat task lists create stress instead of clarity.

4. **Parallel Idea Explosion**
   - Many ideas feel equally important, leading to overload.

---

## Design Principles (Non-Negotiable)

### 1. Projects Are the Primary Container

All work belongs to a **project**.

- Projects own:
  - context
  - conversations
  - tasks
  - history
- Nothing important lives only in chat history.

---

### 2. State > Tasks

Tasks are insufficient alone.

Each project must make it easy to answer:

- What is happening right now?
- What changed recently?
- What is blocked?
- What is the next *small* step?

---

### 3. Safe Suspension Beats Prioritization

The system must support:

- pausing without guilt
- resuming with confidence
- never “losing” ideas

Project states include:

- Active
- Paused
- Incubating
- Blocked

---

### 4. Chat Is a First-Class Input

Chats are:

- design sessions
- planning meetings
- debugging logs

They must be:

- saved automatically
- associated with a project
- readable later without ChatGPT

---

## UI Requirements (Explicit)

The interface is part of the system’s cognitive support.

### Required Layout

1. **Left Panel — Projects**
   - Mirrors ChatGPT’s “Projects” list
   - Shows project status (Active, Paused, etc.)
   - Selecting a project scopes everything else

2. **Secondary Panel — Conversations**
   - Conversations live *under* a project
   - Each conversation has a title and status
   - Switching conversations must not lose draft text

3. **Main Panel — Chat**
   - Visually similar to chatgpt.com
   - Minimal controls:
     - send
     - attach files
     - attach screenshots
   - No clutter, no experimental buttons

4. **Chat Input Box**
   - Large, terminal-like text area
   - Optimized for thinking while typing
   - Inspired by Antigravity’s terminal input
   - Small input boxes are explicitly undesirable

---

## Current Focus

- Improving the UI so it:
  - reduces cognitive friction
  - matches ChatGPT’s project mental model
  - supports long-form thinking while typing

---

## Constraints

- No background autonomy
- No hidden state
- Files remain the source of truth
- UI must reflect filesystem truth, not invent state

---

## Success Definition (Phase 1)

- I can switch projects without anxiety
- I can resume work days later without rereading everything
- I can type freely without feeling constrained by the UI
- The system feels like *external working memory*, not a task manager
