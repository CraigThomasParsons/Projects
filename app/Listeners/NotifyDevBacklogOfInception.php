<?php

namespace App\Listeners;

use App\Events\InceptionCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyDevBacklogOfInception implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * Calls TheDevBacklog's inception webhook so it can open a WritersRoom
     * session and begin tracking the Epic/Story pipeline for this project.
     */
    public function handle(InceptionCompleted $event): void
    {
        $inception = $event->inception;

        // Use base_url (scheme+host only) so we can append our specific webhook path.
        // The 'url' key is the projection-sync endpoint path, not the base URL.
        $devBacklogUrl = $this->resolveDevBacklogBaseUrl();

        if (! $devBacklogUrl) {
            Log::error('NotifyDevBacklogOfInception: DevBacklog base URL not configured. Skipping webhook.');
            return;
        }

        $token = config('services.project_projection.targets');
        $devBacklogToken = null;

        foreach ((array) $token as $target) {
            if (($target['name'] ?? '') === 'devbacklog') {
                $devBacklogToken = $target['token'] ?? null;
                break;
            }
        }

        if (! $devBacklogToken) {
            Log::error('NotifyDevBacklogOfInception: DevBacklog token not configured. Skipping webhook.');
            return;
        }

        $response = Http::withToken($devBacklogToken)
            ->timeout(30)
            ->post(rtrim($devBacklogUrl, '/') . '/api/inception/completed', [
                'project_id'   => $inception->project_id,
                'inception_id' => $inception->id,
            ]);

        if ($response->failed()) {
            Log::error('NotifyDevBacklogOfInception: Webhook call failed.', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return;
        }

        Log::info('NotifyDevBacklogOfInception: WritersRoom session opened in DevBacklog.', [
            'project_id'      => $inception->project_id,
            'inception_id'    => $inception->id,
            'writers_room_id' => $response->json('writers_room_id'),
        ]);
    }

    /**
     * Returns the scheme+host base URL for TheDevBacklog.
     * Reads 'base_url' from the devbacklog target config; falls back to
     * deriving it from the projection-sync URL if 'base_url' is absent.
     */
    private function resolveDevBacklogBaseUrl(): ?string
    {
        foreach ((array) config('services.project_projection.targets') as $target) {
            if (($target['name'] ?? '') === 'devbacklog') {
                // Prefer the explicit base_url; fall back to parsing the sync URL.
                if (! empty($target['base_url'])) {
                    return rtrim((string) $target['base_url'], '/');
                }

                // Derive base from the sync URL as a last resort.
                $syncUrl = $target['url'] ?? null;
                if ($syncUrl) {
                    $parsed = parse_url($syncUrl);
                    return ($parsed['scheme'] ?? 'http') . '://' . ($parsed['host'] ?? '');
                }
            }
        }

        return null;
    }
}
