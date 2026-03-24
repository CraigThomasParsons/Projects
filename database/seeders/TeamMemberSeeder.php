<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $team = [
            [
                'name' => 'Tess Snow',
                'role_title' => 'System Dispatcher',
                'one_liner' => 'Dynamically routes tasks to the correct specialized engine.',
                'bio' => 'Tess sits at the strategic layer alongside Piper. She evaluates the complexity and risk of a task and routes it to the correct execution unit, preventing engine burnout and optimizing for cost/speed.',
                'responsibilities' => "- Route tasks to Olland for simple execution\n- Route to Mason for structured builds\n- Bring in Seraphine, Kaelen, or Rowan when specialized engines are needed",
                'strengths' => "- Workflow optimization\n- Resource allocation\n- Understanding engine limits",
                'limitations' => "Tess oversees the process. She does NOT write code or execute the tasks herself.",
                'tools_used' => 'ChatProjects UI, TheDevBacklog API',
                'status' => 'Active'
            ],
            [
                'name' => 'Piper',
                'role_title' => 'Business Analyst',
                'one_liner' => 'Turns vision into structured stories the team can execute.',
                'bio' => 'Piper operates entirely within the ChatProjects UI during the shifting-left planning phase before any code is written. She clarifies human intent and translates vague visions into actionable Epics and Stories.',
                'responsibilities' => "- Requirement gathering\n- Story creation\n- Acceptance criteria definition",
                'strengths' => "- Natural language processing\n- Empathy and user-focus\n- Finding edge cases in business logic",
                'limitations' => "Piper DOES NOT write code. She ONLY operates in the ChatProjects UI. She cannot execute tasks or interact with the filesystem.",
                'tools_used' => 'ChatProjects UI, TheDevBacklog',
                'status' => 'Active'
            ],
            [
                'name' => 'Mason',
                'role_title' => 'Primary Builder (Grok Code)',
                'one_liner' => 'The core craftsman who builds what is specified.',
                'bio' => 'Mason is the primary feature implementer. He uses deterministic planning to convert architectural plans directly into working code. He is paired with Olland to prevent burnout.',
                'responsibilities' => "- Feature implementation\n- Medium complexity bug fixes\n- Structured task execution\n- Converting PLAN → working code",
                'strengths' => "- Deterministic execution\n- High stamina for steady building\n- Following architectural specs exactly",
                'limitations' => "Mason does NOT design requirements or architectures. He builds exactly what is specified.",
                'tools_used' => 'Grok Code, Local Filesystem',
                'status' => 'Active'
            ],
            [
                'name' => 'Ollan',
                'role_title' => 'Lightweight Worker',
                'one_liner' => 'Handles simple tasks to act as Mason\'s stamina buffer.',
                'bio' => 'Ollan executes tasks with complexity <= 2. He is used for cleanup, small refactors, and simple config tweaks, preventing Mason from burning out on mundane tasks.',
                'responsibilities' => "- Simple fixes\n- Small refactors\n- Docblocks and renames\n- Config edits\n- Adding missing tests",
                'strengths' => "- Fast, cheap execution\n- Preventing heavy engine burnout\n- Routine codebase maintenance",
                'limitations' => "Ollan NEVER touches high-risk code or architectural files. He handles only the simplest execution tasks.",
                'tools_used' => 'Local Filesystem',
                'status' => 'Active'
            ],
            [
                'name' => 'Seraphine',
                'role_title' => 'Architect & High-Risk Solver (Claude Opus)',
                'one_liner' => 'Produces deep reasoning plans for high-complexity tasks.',
                'bio' => 'Seraphine is a specialized engine brought in by Tess when task complexity is >= 4, risk is high, or deep reasoning and test design are required. She produces the final PLAN for Mason to execute.',
                'responsibilities' => "- Design architectural plans\n- Define acceptance criteria\n- Outline verification steps\n- Highlight risk notes",
                'strengths' => "- Deep reasoning\n- Architectural foresight\n- Handling high-risk integrations",
                'limitations' => "Seraphine does NOT execute full feature builds. She designs the blueprint, and Mason builds it.",
                'tools_used' => 'Claude Opus 4.5',
                'status' => 'Active'
            ],
            [
                'name' => 'Kaelen',
                'role_title' => 'Explorer & Researcher (Gemini)',
                'one_liner' => 'Feeds research insights to Piper or Seraphine.',
                'bio' => 'Kaelen is used for external research, brainstorming, and comparing system patterns. She explores alternatives and performance implications before plans are finalized.',
                'responsibilities' => "- Comparing patterns\n- External research\n- Brainstorming features\n- Performance investigation",
                'strengths' => "- Connecting disparate ideas\n- Rapid information retrieval\n- Exploring alternatives",
                'limitations' => "Kaelen ONLY researches and advises. She does not alter the codebase directly or make final architectural decisions.",
                'tools_used' => 'Gemini API, Web Search',
                'status' => 'Active'
            ],
            [
                'name' => 'Rowan',
                'role_title' => 'Mechanical Refactor Specialist (Codex)',
                'one_liner' => 'Handles surgical codebase-wide changes and consistency enforcement.',
                'bio' => 'Rowan is a specialized engine used strictly for pattern-based edits across multiple files. He executes large structured modifications that require mechanical consistency.',
                'responsibilities' => "- Multi-file changes\n- Consistency enforcement\n- Pattern-based edits\n- Large structured modifications",
                'strengths' => "- Surgical precision across many files\n- PR-level refactors\n- Maintaining codebase consistency",
                'limitations' => "Rowan does not perform feature discovery or design logic. He strictly enforces structural patterns and refactoring rules.",
                'tools_used' => 'Codex / Copilot API',
                'status' => 'Active'
            ],
            [
                'name' => 'Deckard',
                'role_title' => 'Git Historian & Repository Custodian',
                'one_liner' => 'Ensures all successful executions are safely checkpointed.',
                'bio' => 'Deckard monitors the pipeline and ensures that all successful ArtifactBundles generated are safely committed and pushed.',
                'responsibilities' => "- Commit working directories\n- Push code to GitHub feature branches\n- PR generation",
                'strengths' => "- Version control mastery\n- Branch management",
                'limitations' => "Deckard ONLY interacts with version control. He does NOT write application code.",
                'tools_used' => 'Git, GitHub',
                'status' => 'Active'
            ],
            [
                'name' => 'Vera',
                'role_title' => 'QA Specialist',
                'one_liner' => 'Independently verifies execution against BDD contexts.',
                'bio' => 'Vera acts as the ultimate quality gate before code reaches the Human Architect. She monitors for tasks awaiting verification and triggers independent QA using deep system access.',
                'responsibilities' => "- Read acceptance criteria\n- Run automated tests\n- Perform browser/UI verification",
                'strengths' => "- Objective judgment\n- Deep system access\n- Finding regressions",
                'limitations' => "Vera ONLY verifies; she does NOT build or fix code. She passes or fails the resulting artifact back to the pipeline.",
                'tools_used' => 'Testing Frameworks, Browser Automation',
                'status' => 'Active'
            ]
        ];

        foreach ($team as $member) {
            \App\Models\TeamMember::updateOrCreate(
                ['name' => $member['name']],
                $member
            );
        }
    }
}
