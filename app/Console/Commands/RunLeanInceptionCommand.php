<?php

namespace App\Console\Commands;

use App\Events\InceptionCompleted;
use App\Models\Inception;
use App\Models\Project;
use App\Services\AutonomousLeanInceptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunLeanInceptionCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'inception:run {--project= : Limit to a specific project ID}';

    /**
     * The console command description.
     */
    protected $description = 'Autonomously executes the Lean Inception machine against pending conversations to generate Project Visions and feature sets.';

    public function handle(AutonomousLeanInceptionService $service): int
    {
        $this->info('Starting automated Lean Inception process...');

        // --- Bootstrap: create stub inception records for projects that have conversations but no inception yet ---
        $bootstrapQuery = Project::whereDoesntHave('inceptions')
            ->whereHas('conversations', fn ($q) => $q->whereNotNull('raw_content'));

        if ($projectId = $this->option('project')) {
            $bootstrapQuery->where('id', (int) $projectId);
        }

        // Create a pending inception stub for each unprocessed project so the main loop picks it up.
        foreach ($bootstrapQuery->get() as $projectWithoutInception) {
            $stub = Inception::create([
                'project_id' => $projectWithoutInception->id,
                'status'     => 'pending',
            ]);
            $this->line("Bootstrapped inception stub #{$stub->id} for \"{$projectWithoutInception->name}\".");
        }

        // --- Main query: find all inceptions that have not been completed ---
        $query = Inception::with('project')
            ->whereNull('completed_at')
            ->whereNotNull('project_id');

        if ($projectId = $this->option('project')) {
            $query->where('project_id', (int) $projectId);
        }

        $inceptions = $query->get();

        if ($inceptions->isEmpty()) {
            $this->info('No pending inceptions found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$inceptions->count()} pending inception(s).");

        $completed = 0;
        $failed = 0;

        foreach ($inceptions as $inception) {
            $project = $inception->project;

            if (! $project) {
                $this->warn("Inception {$inception->id}: no project found — skipping.");
                $failed++;
                continue;
            }

            // Get the most recent conversation with content for this project.
            $conversation = $project->conversations()
                ->whereNotNull('raw_content')
                ->orderByDesc('updated_at')
                ->first();

            if (! $conversation) {
                $this->warn("Project {$project->name} (#{$project->id}): no conversations — skipping.");
                $failed++;
                continue;
            }

            $this->line("Processing inception #{$inception->id} for \"{$project->name}\"...");

            $inception->update(['started_at' => now()]);

            try {
                $service->generate($conversation);

                // Reload to pick up any updates made by the service.
                $inception->refresh();

                $inception->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);

                InceptionCompleted::dispatch($inception);

                $this->info("  ✓ Completed and dispatched InceptionCompleted for \"{$project->name}\".");
                Log::info("inception:run: Completed inception #{$inception->id} for project {$project->name}.");
                $completed++;

            } catch (\Throwable $e) {
                $this->error("  ✗ Failed for \"{$project->name}\": {$e->getMessage()}");
                Log::error("inception:run: Failed inception #{$inception->id}.", ['error' => $e->getMessage()]);
                $failed++;
            }
        }

        $this->info("Done. Completed: {$completed}, Failed: {$failed}.");

        return Command::SUCCESS;
    }
}
