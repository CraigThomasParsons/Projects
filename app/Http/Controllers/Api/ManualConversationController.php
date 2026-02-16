<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Persists manually pasted conversation transcripts for a project.
 *
 * This endpoint exists as a reliability fallback when shared chat links
 * cannot be imported consistently.
 */
final class ManualConversationController extends Controller
{
    /**
     * Store manual transcript content for the target project.
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        // Enforce token auth so only Piper workflows can post transcripts.
        $this->authorizeRequest($request);

        // Validate required transcript payload and optional title.
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'raw_content' => ['required', 'string', 'min:20'],
        ]);

        // Persist as a synthetic conversation record with an explicit source flag.
        $conversation = Conversation::create([
            'project_id' => $project->id,
            'title' => $validated['title'] ?? 'Pasted Conversation',
            'share_url' => 'manual://paste',
            'source_type' => 'manual_paste',
            'raw_content' => $validated['raw_content'],
        ]);

        // Return a compact success payload for machine callers.
        return response()->json([
            'status' => 'ok',
            'conversation_id' => $conversation->id,
        ], 201);
    }

    /**
     * Validate Piper token from either Bearer auth or custom header.
     */
    private function authorizeRequest(Request $request): void
    {
        // Load configured token.
        $token = config('services.piper.token');

        // Stop immediately when service auth is not configured.
        if (empty($token)) {
            abort(500, 'Piper token not configured.');
        }

        // Accept both bearer and custom header formats for compatibility.
        $provided = $request->bearerToken()
            ?? $request->header('X-Piper-Token');

        // Deny requests with mismatched credentials.
        if ($provided !== $token) {
            abort(403, 'Invalid token.');
        }
    }
}
