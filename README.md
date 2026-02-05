# Projects
This repository contains the code to run the Chat-First Project Manager application.
The chat doesn't connect to chatGPT first to have a conversation, you need to share conversations
with the Projects application after creating them, then it will sync the conversation locally.

---

# Chat-First Project Manager

A chat-centric project management and thinking environment built with Laravel.

This project exists to support a workflow where **conversations are the primary unit of work**.  
Instead of forcing ideas into tickets up front, it allows planning, execution, notes, code, images,
and progress tracking to live together naturally — the same way developers actually think.

---

## Why This Exists

Traditional tools like Jira, Linear, or Trello assume:
- tasks are well-defined early
- work progresses linearly
- conversation is secondary

This project flips that model:

> **Conversation comes first. Structure emerges later.**

If you already find yourself using ChatGPT conversations as:
- TODO lists
- design documents
- rubber-duck debugging
- progress trackers

…this tool is designed for you.

---

## Core Concepts

### Projects
A project is a container for related conversations.

### Conversations
A conversation is a persistent workspace for:
- brainstorming
- planning
- execution
- reflection

Conversations are not ephemeral chat logs — they are living documents.

### Messages
Messages support:
- Markdown
- Code blocks
- Images
- Attachments
- Checklists

Messages are intentionally flexible and low-friction.

---

## Markdown Checklists (First-Class Feature)

Messages support GitHub-style markdown checkboxes:

```md
- [ ] Reset GitHub connector
- [x] Decide Playwright architecture
- [ ] Implement WebSocket protocol
```

Checkboxes:
- are rendered as real UI elements
- can be toggled without editing message text
- provide immediate visual feedback
- turn conversations into lightweight TODO lists

This makes progress visible *inside* the conversation, not in a separate tool.

---

## Design Philosophy

- Chat is an **action surface**, not just history
- Progress should be visible where thinking happens
- Low friction beats rigid structure
- Structure should be optional and derived, not enforced
- Friendly to iterative and ADHD-style workflows

---

## Tech Stack

- Laravel 11
- Livewire
- Alpine.js
- PHP 8.3+
- Markdown rendering

Planned or experimental additions:
- WebSocket streaming
- Browser-automated AI interaction (Playwright sidecar)
- Artifact extraction (code, images, diffs)
- Conversation-level progress indicators

---

## Status

Early-stage and experimental.

This is currently built for personal use and exploration,
with an emphasis on correctness, clarity, and long-term extensibility.

---

## License

MIT
# Chat-First Project Manager

A chat-centric project management and thinking environment built with Laravel.

This project exists to support a workflow where **conversations are the primary unit of work**.  
Instead of forcing ideas into tickets up front, it allows planning, execution, notes, code, images,
and progress tracking to live together naturally — the same way developers actually think.

---

## Why This Exists

Traditional tools like Jira, Linear, or Trello assume:
- tasks are well-defined early
- work progresses linearly
- conversation is secondary

This project flips that model:

> **Conversation comes first. Structure emerges later.**

If you already find yourself using ChatGPT conversations as:
- TODO lists
- design documents
- rubber-duck debugging
- progress trackers

…this tool is designed for you.

---

## Core Concepts

### Projects
A project is a container for related conversations.

### Conversations
A conversation is a persistent workspace for:
- brainstorming
- planning
- execution
- reflection

Conversations are not ephemeral chat logs — they are living documents.

### Messages
Messages support:
- Markdown
- Code blocks
- Images
- Attachments
- Checklists

Messages are intentionally flexible and low-friction.

---

## Markdown Checklists (First-Class Feature)

Messages support GitHub-style markdown checkboxes:

```md
- [ ] Reset GitHub connector
- [x] Decide Playwright architecture
- [ ] Implement WebSocket protocol
```

Checkboxes:
- are rendered as real UI elements
- can be toggled without editing message text
- provide immediate visual feedback
- turn conversations into lightweight TODO lists

This makes progress visible *inside* the conversation, not in a separate tool.

---

## Design Philosophy

- Chat is an **action surface**, not just history
- Progress should be visible where thinking happens
- Low friction beats rigid structure
- Structure should be optional and derived, not enforced
- Friendly to iterative and ADHD-style workflows

---

## Tech Stack

- Laravel 11
- Livewire
- Alpine.js
- PHP 8.3+
- Markdown rendering

Planned or experimental additions:
- WebSocket streaming
- Browser-automated AI interaction (Playwright sidecar)
- Artifact extraction (code, images, diffs)
- Conversation-level progress indicators

---

## Status

Early-stage and experimental.

This is currently built for personal use and exploration,
with an emphasis on correctness, clarity, and long-term extensibility.

---

## License

MIT
