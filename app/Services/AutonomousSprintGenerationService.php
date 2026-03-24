<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Inception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Automates the creation of Agile Sprints from completed Lean Inceptions.
 * 
 * Replaces the external Python Piper daemon to generate Epics and Stories natively
 * using the configured LLM, and pushes them directly to TheWritersRoom API.
 */
class AutonomousSprintGenerationService
{
    /**
     * Executes the zero-shot prompt pipeline to generate and persist a Sprint.
     */
    public function generate(Inception $inception): void
    {
        $project = $inception->project;

        if (!$project) {
            Log::warning("Cannot generate sprint: Inception {$inception->id} has no project.");
            return;
        }

        // Build context from the finalized inception metadata
        $visionText = $this->buildVisionContext($inception);
        $personasText = $this->buildPersonasContext($inception);
        $featuresText = $this->buildFeaturesContext($inception);

        if (empty(trim($featuresText))) {
            Log::warning("AutonomousSprintGenerationService: No 'Must Have' or 'Should Have' features found for project {$project->name}. Aborting sprint generation.");
            return;
        }

        $prompt = "You are Piper, an expert Agile Software Architect.
Your job is to read the attached Lean Inception document and break it down into an actionable Agile Sprint containing well-structured Epics and User Stories.
Provide the output strictly as a JSON object matching this schema EXACTLY. Do NOT wrap in markdown code blocks.

{
  \"epics\": [
    {
      \"title\": \"Epic Name\",
      \"summary\": \"Brief description of the epic\",
      \"stories\": [
        {
          \"title\": \"Story Title\",
          \"narrative\": \"As a [persona], I want [action] so that [reason]\",
          \"acceptance_criteria\": \"- Criterion 1\\n- Criterion 2\",
          \"persona_key\": \"lowercase_underscored_key\",
          \"persona_name\": \"User Type Name\",
          \"priority\": 1,
          \"est_points\": 3,
          \"status_key\": \"ready\"
        }
      ]
    }
  ]
}

INCEPTION CONTEXT:
{$visionText}

PERSONAS:
{$personasText}

FEATURES TO IMPLEMENT:
{$featuresText}";

        Log::info("AutonomousSprintGenerationService: Requesting LLM sprint generation for project {$project->name}...");

        Log::info("AutonomousSprintGenerationService: Requesting LLM sprint generation for project {$project->name}...");

        // Retrieve the configured endpoint; default to OpenAI if missing to prevent silent failures
        $baseApiUrl = rtrim(config('services.ai.base_url', 'https://api.openai.com/v1'), '/');
        
        // Execute the external request requiring strict structured JSON output to ensure predictability
        $llmResponse = Http::withToken(config('services.ai.api_key'))
            ->timeout(120)
            ->post($baseApiUrl . '/chat/completions', [
                'model' => config('services.ai.model', 'gpt-4o'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a JSON-only response bot. Output valid JSON matching the requested schema exactly.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
            ]);

        // Guard clause: Fail fast if the upstream generative API returns anything but 200 OK
        if ($llmResponse->failed()) {
            Log::error("Failed to generate Sprint via LLM.", [
                'status' => $llmResponse->status(),
                'body' => $llmResponse->body()
            ]);
            return;
        }

        // Isolate the core JSON payload emitted by the model's message content
        $rawJsonPayload = $llmResponse->json('choices.0.message.content');

        // Guard clause: Reject malformed or empty responses
        if (!$rawJsonPayload) {
             Log::error("No valid choices returned from LLM for sprint generation.");
             return;
        }

        // Securely decode the JSON string into an associative array for manipulation
        $decodedSprintPayload = json_decode($rawJsonPayload, true);

        // Guard clause: Ensure the decoding worked and the expected 'epics' root node exists
        if ($decodedSprintPayload === null || !isset($decodedSprintPayload['epics'])) {
            Log::error("Failed to parse valid Epics JSON out of LLM response.", ['raw' => $rawJsonPayload]);
            return;
        }

        // Route the finalized payload to the external WritersRoom instance via its API endpoint
        $this->pushToWritersRoom($project->id, $decodedSprintPayload);
    }

    /**
     * Helper to concatenate vision, business goals, and success metrics.
     */
    private function buildVisionContext(Inception $inception): string
    {
        $context = "";
        if ($inception->vision_statement) {
            $context .= "Vision Statement: {$inception->vision_statement}\n";
        }
        if ($inception->business_goals) {
            $context .= "Business Goals: {$inception->business_goals}\n";
        }
        if ($inception->success_metrics) {
            $context .= "Success Metrics: {$inception->success_metrics}\n";
        }
        return $context;
    }

    /**
     * Helper to list out current personas.
     */
    private function buildPersonasContext(Inception $inception): string
    {
        $personas = $inception->personas()->get();
        if ($personas->isEmpty()) {
            return "No specific personas identified.";
        }

        $context = "";
        foreach ($personas as $persona) {
            $context .= "- {$persona->name} (Tech Level: {$persona->tech_level})\n";
            $context .= "  Goals: {$persona->goals}\n";
            $context .= "  Frustrations: {$persona->frustrations}\n";
        }
        return $context;
    }

    /**
     * Helper to list out actionable features.
     */
    private function buildFeaturesContext(Inception $inception): string
    {
        // Only generate stories for features prioritized for the MVP
        $features = $inception->features()
            ->whereIn('mvp_status', ['Must Have', 'Should Have'])
            ->get();

        $context = "";
        foreach ($features as $feature) {
            $context .= "- Title: {$feature->title}\n";
            $context .= "  Description: {$feature->description}\n";
            $context .= "  Priority: {$feature->mvp_status}\n";
        }
        return $context;
    }

    /**
     * Transmits the standardized Sprint payload to TheWritersRoom machine endpoint.
     */
    private function pushToWritersRoom(int $projectId, array $epicsPayload): void
    {
        Log::info("AutonomousSprintGenerationService: Pushing structured Epics to TheWritersRoom for Project {$projectId}...");

        $writersRoomSyncUrl = null;
        // Scan configured remote targets to locate the WritersRoom sync endpoint
        $configuredTargets = config('services.project_projection.targets', []);
        
        foreach ($configuredTargets as $projectionTarget) {
            if (($projectionTarget['name'] ?? '') === 'writersroom') {
                $writersRoomSyncUrl = $projectionTarget['url'];
                break;
            }
        }

        // Guard clause: Stop execution if the environment lacks the remote WritersRoom routing configuration
        if (!$writersRoomSyncUrl) {
            Log::error("WritersRoom sync url not found in projection targets.");
            return;
        }

        // Transform the projection URL to the Piper epic target endpoint.
        // Usually, WRITERSROOM_PROJECT_SYNC_URL is: http://stories.elasticgun.com/api/projects/projection-sync
        // We replace '/projects/projection-sync' with "/piper/projects/{projectId}/epics-stories" to hit the pipeline.
        $piperTargetUrl = str_replace(
            '/projects/projection-sync', 
            "/piper/projects/{$projectId}/epics-stories", 
            $writersRoomSyncUrl
        );

        // Execute the transmission POST directly to TheWritersRoom, relying on the central Piper machine auth token
        $transmissionResponse = Http::withToken(config('services.piper.token'))
            ->timeout(60)
            ->post($piperTargetUrl, $epicsPayload);

        // Guard clause: Ensure WritersRoom successfully persisted everything before marking complete
        if ($transmissionResponse->failed()) {
            Log::error("Failed to push Epics and Stories to TheWritersRoom API.", [
                'status' => $transmissionResponse->status(),
                'body' => $transmissionResponse->body(),
                'url' => $piperTargetUrl
            ]);
            return;
        }

        Log::info("AutonomousSprintGenerationService: Successfully pushed Epics to TheWritersRoom.", [
            'response' => $transmissionResponse->json()
        ]);
    }
}
