<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Project;
use App\Services\ProjectProjectionWebhookDispatcher;

/**
 * Emits canonical project webhook events after model lifecycle transitions.
 */
final class ProjectObserver
{
    public function __construct(
        private readonly ProjectProjectionWebhookDispatcher $webhookDispatcher
    ) {
    }

    /**
     * Broadcast create/update transitions as projection upserts.
     */
    public function saved(Project $project): void
    {
        // Do not emit upserts for soft-deleted records during delete transition.
        if ($project->deleted_at !== null) {
            return;
        }

        $this->webhookDispatcher->dispatchUpsert($project);
    }

    /**
     * Broadcast soft-deletes so downstream projections mirror archive state.
     */
    public function deleted(Project $project): void
    {
        $this->webhookDispatcher->dispatchDeleted($project);
    }

    /**
     * Broadcast restore transitions as projection upserts.
     */
    public function restored(Project $project): void
    {
        $this->webhookDispatcher->dispatchUpsert($project);
    }
}
