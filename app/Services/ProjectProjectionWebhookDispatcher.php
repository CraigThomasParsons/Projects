<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Dispatches canonical project change events to downstream projection consumers.
 *
 * This service emits idempotent upsert/delete webhook payloads to
 * WritersRoom and DevBacklog so projections converge in near-real-time.
 */
final class ProjectProjectionWebhookDispatcher
{
    /**
     * Dispatch an upsert event for create/update/restore transitions.
     */
    public function dispatchUpsert(Project $project): void
    {
        $this->dispatch('project.upsert', $project);
    }

    /**
     * Dispatch a delete event for soft-deleted projects.
     */
    public function dispatchDeleted(Project $project): void
    {
        $this->dispatch('project.deleted', $project);
    }

    /**
     * Send the requested event to each configured projection consumer.
     */
    private function dispatch(string $eventType, Project $project): void
    {
        $targets = (array) config('services.project_projection.targets', []);
        $timeoutSeconds = (int) config('services.project_projection.timeout_seconds', 8);

        if ($targets === []) {
            return;
        }

        $sourceUpdatedAt = $project->updated_at?->toIso8601String() ?? now()->toIso8601String();

        $payload = [
            'event' => $eventType,
            'project' => [
                'id' => $project->id,
                'project_uuid' => $project->project_uuid,
                'name' => $project->name,
                'description' => $project->description,
                'code_folder' => $project->code_folder,
                'local_location' => $project->local_location,
                'github_repo' => $project->github_repo,
                'gitea_location' => $project->gitea_location,
                'framework_description' => $project->framework_description,
                'languages' => $project->languages,
                'source_updated_at' => $sourceUpdatedAt,
                'deleted_at' => $project->deleted_at?->toIso8601String(),
            ],
        ];

        // Stable idempotency key avoids duplicate processing across retries.
        $idempotencyKey = hash('sha256', implode('|', [
            $eventType,
            (string) $project->project_uuid,
            $sourceUpdatedAt,
        ]));

        foreach ($targets as $target) {
            $targetUrl = trim((string) Arr::get($target, 'url', ''));
            $targetName = (string) Arr::get($target, 'name', 'unknown');
            $targetToken = trim((string) Arr::get($target, 'token', ''));

            // Skip unconfigured targets so partial environments keep working.
            if ($targetUrl === '') {
                continue;
            }

            try {
                $request = Http::acceptJson()
                    ->timeout($timeoutSeconds)
                    ->withHeader('X-Idempotency-Key', $idempotencyKey)
                    ->withHeader('X-Project-Registry-Event', $eventType);

                if ($targetToken !== '') {
                    $request = $request->withToken($targetToken);
                }

                $response = $request->post($targetUrl, $payload);

                if ($response->failed()) {
                    Log::warning('Project projection webhook failed.', [
                        'target' => $targetName,
                        'url' => $targetUrl,
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'project_id' => $project->id,
                        'event' => $eventType,
                        'idempotency_key' => $idempotencyKey,
                    ]);
                }
            } catch (\Throwable $throwable) {
                Log::warning('Project projection webhook request failed.', [
                    'target' => $targetName,
                    'url' => $targetUrl,
                    'project_id' => $project->id,
                    'event' => $eventType,
                    'idempotency_key' => $idempotencyKey,
                    'error' => $throwable->getMessage(),
                ]);
            }
        }
    }
}
