# Projects

The ChatGPT Webexperience locally.

# Chat-First Project Manager

A lightweight, chat-centric project management tool built with Laravel.

This project treats **conversations as the primary unit of work**, allowing
ideas, tasks, code snippets, and progress to live together naturally —
without forcing everything into tickets or rigid workflows.

---

## ✨ Core Concepts

- **Projects** contain conversations
- **Conversations** are long-lived thinking spaces
- **Messages** support:
  - Markdown
  - Code snippets
  - Images
  - Checklists
- **Checkboxes live inside chat**, not in a separate task system

This tool is designed to match how developers *actually* work —
iteratively, non-linearly, and context-first.

---

## ✅ Markdown Checkboxes

Messages support GitHub-style markdown checkboxes:

- [ ] Do the thing
- [x] Already done


Checkbox state is stored separately from message content, allowing:

- Reliable toggling

- Progress tracking

- Future promotion to tasks (optional)
