<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Provides Piper with project context and conversation source material.
 *
 * This endpoint is intentionally read-only.
 * It exposes project metadata and stored conversation content,
 * including manual pastes when share sync is incomplete.
 */
final class PiperProjectInputController extends Controller
{
    /**
     * Return structured project input for Piper.
     */
    public function show(Request $request, Project $project): JsonResponse
    {
        // Enforce machine-to-machine token auth to avoid accidental public access.
        $this->authorizeRequest($request);

        // Load conversations newest-first so Piper can prioritize the latest context.
        $project->load(['conversations' => function ($query) {
            $query->latest();
        }]);

        // Shape a stable response payload to decouple Piper from internal model structure.
        return response()->json([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'code_context' => [
                    'local_location' => $project->local_location,
                    'github_repo' => $project->github_repo,
                    'gitea_location' => $project->gitea_location,
                    'framework_description' => $project->framework_description,
                    'languages' => $project->languages,
                ],
            ],
            // Return normalized conversation records with source information.
            'conversations' => $project->conversations->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'source_type' => $conversation->source_type,
                    'share_url' => $conversation->share_url,
                    'raw_content' => $conversation->raw_content,
                    'updated_at' => $conversation->updated_at,
                ];
            })->values(),
        ]);
    }

    /**
     * Validate Piper token from either Bearer auth or custom header.
     */
    private function authorizeRequest(Request $request): void
    {
        // Resolve configured secret so environment-level auth remains centralized.
        $token = config('services.piper.token');

        // Fail fast when token configuration is missing.
        if (empty($token)) {
            abort(500, 'Piper token not configured.');
        }

        // Support both standard bearer auth and legacy custom header usage.
        $provided = $request->bearerToken()
            ?? $request->header('X-Piper-Token');

        // Reject mismatched tokens immediately.
        if ($provided !== $token) {
            abort(403, 'Invalid token.');
        }
    }
}
