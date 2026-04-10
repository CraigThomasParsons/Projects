<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Provides Krax Bridge with extreme hard context (Lean Inception data)
 * as well as the soft context (conversation source material).
 *
 * This explicit division ensures LLM workflows do not hallucinate
 * previously defined features or scope.
 */
final class KraxInputController extends Controller
{
    /**
     * Return structured project inputs encompassing conversations and inception data for Krax.
     */
    public function show(Request $request, Project $project): JsonResponse
    {
        // Enforce token auth to avoid accidental public access.
        $this->authorizeRequest($request);

        // Load conversations newest-first so the extraction agent can prioritize the latest context.
        // Crucially, eager load the inception data (personas, features, artifacts).
        $project->load([
            'conversations' => function ($query) {
                $query->latest();
            },
            'inceptions.personas',
            'inceptions.features',
        ]);

        // We only care about the latest active inception record (if multiple exist).
        $activeInception = $project->inceptions->last();

        // Shape a heavily structured payload.
        // Hard Context: The deterministic structured data from Lean Inception.
        // Soft Context: The underlying conversation text.
        return response()->json([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'code_context' => [
                    'local_location' => $project->local_location,
                    'github_repo' => $project->github_repo,
                    'framework_description' => $project->framework_description,
                    'languages' => $project->languages,
                ],
            ],
            'inception' => $activeInception ? [
                'id' => $activeInception->id,
                'business_goals' => $activeInception->business_goals,
                'success_metrics' => $activeInception->success_metrics,
                'vision_statement' => $activeInception->vision_statement,
                'mvp_canvas' => $activeInception->mvp_canvas,
                'personas' => $activeInception->personas->map(function ($persona) {
                    return [
                        'name' => $persona->name,
                        'profile' => $persona->profile,
                        'needs' => $persona->needs,
                    ];
                })->values(),
                'features' => $activeInception->features->map(function ($feature) {
                    return [
                        'name' => $feature->name,
                        'description' => $feature->description,
                        'value' => $feature->business_value,
                        'effort' => $feature->effort,
                    ];
                })->values(),
            ] : null,
            // Return normalized conversation records with source information.
            'conversations' => $project->conversations->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'raw_content' => $conversation->raw_content,
                    'updated_at' => $conversation->updated_at,
                ];
            })->values(),
        ]);
    }

    /**
     * Validate bridge token from Bearer auth or custom header.
     */
    private function authorizeRequest(Request $request): void
    {
        // Resolve configured secret so environment-level auth remains centralized.
        // Re-using the piper token for now since they are part of the same data extraction plane,
        // but looking it up under a krax alias is acceptable if defined.
        $token = config('services.piper.token') ?? config('services.krax.token');

        // Fail fast when token configuration is missing.
        if (empty($token)) {
            abort(500, 'Bridge execution token not configured.');
        }

        // Support both standard bearer auth and legacy custom header usage.
        $provided = $request->bearerToken()
            ?? $request->header('X-Piper-Token')
            ?? $request->header('X-Krax-Token');

        // Reject mismatched tokens immediately.
        if ($provided !== $token) {
            abort(403, 'Invalid token.');
        }
    }
}
