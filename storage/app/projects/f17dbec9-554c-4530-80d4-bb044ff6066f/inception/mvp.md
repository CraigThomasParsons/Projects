# MVP Canvas (MoSCoW)

## Must Have (Core to the MVP)

- **"The Team" Page (Agent Roster)**
  - A dedicated ChatProjects UI feature acting as the structural home for all agents. Includes TeamMember profiles with explicit fields for limitations, responsibilities, and strengths to prevent "idea drift".
- **Mason → Ollan Handshake Protocol (v1.0)**
  - The strict deterministic infrastructure where Mason generates an immutable TaskPacket and Ollan executes it exactly once in a sandboxed environment without self-reflection.
- **Sprint completion**
  - Run a sprint and loop through each feature/ticket until it done

## Should Have (Important, but not vital)

- **Vera Verification Trigger**
  - The system monitors tasks transitioning to awaiting_verification and triggers Vera to perform independent QA using deep system access.

## Could Have (Nice to have)

- **In-Ticket Agent Collaboration UI**
  - UI elements (StoryTask comments and thoughts.md tab) to reveal the "why" behind an agent's actions without cluttering the main story ticket.
- **Ticket-Level Scope Lock**
  - Context is locked down per ticket rather than per sprint. Agents execute deterministically against the ticket goal, eliminating the need for artificial sprint boundaries.
- **Deckard's Git Integration**
  - Automated checkpoint commits triggered by successful executions, pushing to feature branches and laying groundwork for advanced Gitflow.

## Won't Have (Out of scope for this version)

- **Human Oversight Gatekeeper (GitHub PRs)**
  - The pipeline automatically commits verified code to a feature branch and opens a Pull Request. The human Architect reviews the PR for final sanity checks before merging.

