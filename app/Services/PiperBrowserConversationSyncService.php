<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Queues browser-driven conversation extraction through Piper.
 *
 * This service handles non-share ChatGPT links where server-side fetching
 * cannot access private conversation content directly.
 */
final class PiperBrowserConversationSyncService
{
    /**
     * Request a browser extraction job for the given conversation.
     *
     * @throws RuntimeException
     */
    public function queueExtraction(Conversation $conversation): void
    {
        $syncEndpointUrl = (string) config('services.piper.browser_sync_url');

        // Guard clause: fail fast when Piper browser sync endpoint is not configured.
        if ($syncEndpointUrl === '') {
            throw new RuntimeException('Piper browser sync endpoint is not configured.');
        }

        $piperToken = (string) config('services.piper.token');

        // Guard clause: token is required for protected import callback.
        if ($piperToken === '') {
            throw new RuntimeException('Piper token is not configured.');
        }

        $appBaseUrl = rtrim((string) config('app.url'), '/');

        // Guard clause: callback URL cannot be built without app base URL.
        if ($appBaseUrl === '') {
            throw new RuntimeException('APP_URL is not configured.');
        }

        $importCallbackUrl = $appBaseUrl . '/api/conversations/' . $conversation->id . '/import';

        // Send extraction request to Piper so the browser plugin can collect content.
        $response = Http::timeout(30)
            ->acceptJson()
            ->post($syncEndpointUrl, [
                'project_id' => $conversation->project_id,
                'conversation_id' => $conversation->id,
                'conversation_url' => $conversation->share_url,
                'import_callback_url' => $importCallbackUrl,
                'import_callback_token' => $piperToken,
                'mode' => 'chat_link_extract',
            ]);

        // Guard clause: bubble detailed response body on any upstream failure.
        if ($response->failed()) {
            throw new RuntimeException(
                'Piper browser sync request failed: ' . $response->status() . ' ' . $response->body()
            );
        }
    }
}
