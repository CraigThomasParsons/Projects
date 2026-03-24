<?php

namespace App\Listeners;

use App\Events\InceptionCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Support\Facades\Log;

class GenerateSprintsFromInception implements ShouldQueue
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
    public function handle(InceptionCompleted $event): void
    {
        $inception = $event->inception;
        $project = $inception->project;

        Log::info("GenerateSprintsFromInception Listener: Autonomous Sprint Generation started for project {$project->name}.");
        
        app(\App\Services\AutonomousSprintGenerationService::class)->generate($inception);
    }
}
