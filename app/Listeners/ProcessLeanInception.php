<?php

namespace App\Listeners;

use App\Events\ConversationImported;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Support\Facades\Log;

class ProcessLeanInception implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ConversationImported $event): void
    {
        $conversation = $event->conversation;
        $project = $conversation->project;

        if (!$project) {
            return;
        }

        // CRITICAL GUARD: Only auto-process Idea projects, strictly ignoring Unassigned
        if (strtolower($project->name) === 'unassigned' || $project->type !== 'idea') {
            Log::info("ProcessLeanInception Listener: Skipping conversation {$conversation->id}, project '{$project->name}' is not an applicable Idea project.");
            return;
        }

        Log::info("ProcessLeanInception Listener: Starting autonomous inception for {$project->name} from conversation {$conversation->id}.");
        
        // Trigger native AI prompt pipeline
        app(\App\Services\AutonomousLeanInceptionService::class)->generate($conversation);
    }
}
