<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Exposes canonical project records for downstream service projections.
 */
final class ProjectRegistryController extends Controller
{
    private const SCHEMA_VERSION = '2026-02-16.project-registry.v1';

    /**
     * List canonical projects with optional sync filters.
     */
    public function index(Request $request): JsonResponse
    {
        $includeDeleted = $request->boolean('include_deleted', false);
        $updatedSinceRaw = trim((string) $request->query('updated_since', ''));

        $projectsQuery = Project::query();

        if ($includeDeleted) {
            $projectsQuery->withTrashed();
        }

        if ($updatedSinceRaw !== '') {
            try {
                $updatedSince = CarbonImmutable::parse($updatedSinceRaw);

                $projectsQuery->where(function ($query) use ($updatedSince) {
                    $query->where('updated_at', '>=', $updatedSince)
                        ->orWhere('deleted_at', '>=', $updatedSince);
                });
            } catch (\Throwable) {
                return response()->json([
                    'schema_version' => self::SCHEMA_VERSION,
                    'error' => 'Invalid updated_since value. Use ISO-8601 date/time.',
                ], 422);
            }
        }

        $projects = $projectsQuery
            ->orderBy('id')
            ->get();

        return response()->json([
            'schema_version' => self::SCHEMA_VERSION,
            'generated_at' => now()->toIso8601String(),
            'count' => $projects->count(),
            'data' => $projects->map(fn (Project $project) => $this->serializeProject($project))->values(),
        ]);
    }

    /**
     * Return one canonical project by id or UUID.
     */
    public function show(Request $request, string $projectIdentifier): JsonResponse
    {
        $includeDeleted = $request->boolean('include_deleted', false);

        $projectsQuery = Project::query();

        if ($includeDeleted) {
            $projectsQuery->withTrashed();
        }

        $project = $projectsQuery
            ->where(function ($query) use ($projectIdentifier) {
                $query->where('project_uuid', $projectIdentifier);

                if (ctype_digit($projectIdentifier)) {
                    $query->orWhere('id', (int) $projectIdentifier);
                }
            })
            ->first();

        if ($project === null) {
            return response()->json([
                'schema_version' => self::SCHEMA_VERSION,
                'error' => 'Project not found.',
            ], 404);
        }

        return response()->json([
            'schema_version' => self::SCHEMA_VERSION,
            'generated_at' => now()->toIso8601String(),
            'data' => $this->serializeProject($project),
        ]);
    }

    /**
     * Normalize project payload shape for cross-service consumers.
     *
     * @return array<string, mixed>
     */
    private function serializeProject(Project $project): array
    {
        return [
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
            'created_at' => optional($project->created_at)?->toIso8601String(),
            'updated_at' => optional($project->updated_at)?->toIso8601String(),
            'deleted_at' => optional($project->deleted_at)?->toIso8601String(),
        ];
    }
}
