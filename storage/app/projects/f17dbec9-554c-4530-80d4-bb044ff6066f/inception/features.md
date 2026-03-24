# Brainstormed Features

| Title | Value | Effort | Score | Description |
|---|---|---|---|---|
| "The Team" Page (Agent Roster) | 8 | 5 | 1.6 | A dedicated ChatProjects UI feature acting as the structural home for all agents. Includes TeamMember profiles with explicit fields for limitations, responsibilities, and strengths to prevent "idea drift". |
| Mason → Ollan Handshake Protocol (v1.0) | 10 | 8 | 1.3 | The strict deterministic infrastructure where Mason generates an immutable TaskPacket and Ollan executes it exactly once in a sandboxed environment without self-reflection. |
| Human Oversight Gatekeeper (GitHub PRs) | 10 | 3 | 3.3 | The pipeline automatically commits verified code to a feature branch and opens a Pull Request. The human Architect reviews the PR for final sanity checks before merging. |
| Vera Verification Trigger | 7 | 9 | 0.8 | The system monitors tasks transitioning to awaiting_verification and triggers Vera to perform independent QA using deep system access. |
| Sprint completion | 5 | 5 | 1.0 | Run a sprint and loop through each feature/ticket until it done |
| In-Ticket Agent Collaboration UI | 9 | 4 | 2.3 | UI elements (StoryTask comments and thoughts.md tab) to reveal the "why" behind an agent's actions without cluttering the main story ticket. |
| Ticket-Level Scope Lock | 10 | 2 | 5.0 | Context is locked down per ticket rather than per sprint. Agents execute deterministically against the ticket goal, eliminating the need for artificial sprint boundaries. |
| Deckard's Git Integration | 8 | 4 | 2.0 | Automated checkpoint commits triggered by successful executions, pushing to feature branches and laying groundwork for advanced Gitflow. |
