<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Conversation;
use App\Models\Inception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutonomousLeanInceptionService
{
    /**
     * Executes the zero-shot prompt pipeline to generate and persist a Lean Inception.
     */
    public function generate(Conversation $conversation): void
    {
        $project = $conversation->project;
        $content = substr($conversation->raw_content ?? '', 0, 30000); // Prevent massive payloads

        if (empty(trim($content))) {
            Log::warning("Cannot generate inception: Conversation {$conversation->id} is empty.");
            return;
        }

        $prompt = "You are an expert Agile Product Manager and Lean Inception facilitator.
Your job is to read the attached raw conversation transcript and distill it into a structured Lean Inception document.
Provide the output strictly as a JSON object matching this schema exactly. Do NOT wrap in markdown code blocks.

{
  \"vision\": {
    \"vision_statement\": \"A 1-2 sentence compelling product vision.\",
    \"business_goals\": \"A list of the primary business objectives.\",
    \"success_metrics\": \"How we will measure success (KPIs).\"
  },
  \"personas\": [
    {
      \"name\": \"Role or Character Name\",
      \"tech_level\": \"Low/Medium/High\",
      \"goals\": \"What they want to achieve\",
      \"frustrations\": \"Their current pain points\",
      \"context\": \"When/where they use the product\"
    }
  ],
  \"features\": [
    {
      \"title\": \"Short feature name\",
      \"description\": \"1 sentence detail\",
      \"value_score\": 5, // 1 to 5
      \"effort_score\": 3, // 1 to 5
      \"mvp_status\": \"Must Have\" // Must Have, Should Have, Could Have, Won't Have
    }
  ]
}

TRANSCRIPT:
{$content}";

        Log::info("AutonomousLeanInceptionService: Requesting Claude completions for project {$project->name}...");

        $baseUrl = rtrim(config('services.anthropic.base_url', 'https://api.anthropic.com/v1'), '/');
        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
        ])
            ->timeout(120)
            ->post($baseUrl . '/messages', [
                'model' => config('services.anthropic.model', 'claude-opus-4-6'),
                'max_tokens' => 4096,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
            ]);

        if ($response->failed()) {
            Log::error("Failed to generate Lean Inception via Claude.", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return;
        }

        $data = $response->json('content.0.text');

        if (!$data) {
             Log::error("No valid content returned from Claude.");
             return;
        }

        $parsed = json_decode($data, true);

        if (!$parsed) {
            Log::error("Failed to parse JSON out of Claude response.", ['raw' => $data]);
            return;
        }

        $this->persistInception($project, $parsed);
    }

    private function persistInception($project, array $data): void
    {
        Log::info("AutonomousLeanInceptionService: Persisting Inception data to database...");

        // Create or update the core Inception record
        $inception = $project->inceptions()->firstOrCreate(
            [], // Conditions
            ['status' => 'mvp'] // Assume it reaches the MVP canvas stage autonomously
        );

        if (isset($data['vision'])) {
            $vg = $data['vision']['business_goals'] ?? null;
            $sm = $data['vision']['success_metrics'] ?? null;

            $inception->update([
                'vision_statement' => $data['vision']['vision_statement'] ?? null,
                'business_goals' => is_array($vg) ? implode("\n- ", $vg) : $vg,
                'success_metrics' => is_array($sm) ? implode("\n- ", $sm) : $sm,
                'status' => 'mvp'
            ]);
        }

        // Add Personas
        if (isset($data['personas']) && is_array($data['personas'])) {
            // Clear existing to prevent duplicates on re-syncs
            $inception->personas()->delete();
            
            foreach ($data['personas'] as $personaData) {
                // The LLM may return arrays for text fields; flatten them to newline-separated strings.
                $inception->personas()->create([
                    'name'         => $personaData['name'] ?? 'Unknown Persona',
                    'tech_level'   => $personaData['tech_level'] ?? null,
                    'goals'        => $this->flattenToString($personaData['goals'] ?? null),
                    'frustrations' => $this->flattenToString($personaData['frustrations'] ?? null),
                    'context'      => $this->flattenToString($personaData['context'] ?? null),
                ]);
            }
        }

        // Add Features
        if (isset($data['features']) && is_array($data['features'])) {
            $inception->features()->delete();

            foreach ($data['features'] as $featureData) {
                $inception->features()->create([
                    'title' => $featureData['title'] ?? 'Untitled Feature',
                    'description' => $featureData['description'] ?? null,
                    'value_score' => $featureData['value_score'] ?? 3,
                    'effort_score' => $featureData['effort_score'] ?? 3,
                    'mvp_status' => $featureData['mvp_status'] ?? 'Could Have',
                ]);
            }
        }

        Log::info("AutonomousLeanInceptionService: Successfully persisted completed Lean Inception for {$project->name}.");

        // Auto-generate the flat artifacts right after so the filesystem reflects the database
        app(InceptionArtifactGenerator::class)->generate($inception);
        Log::info("AutonomousLeanInceptionService: Flat artifacts written for {$project->name}.");
    }

    /**
     * Ensures a value intended for a text column is a plain string.
     * LLMs sometimes return arrays where scalars are expected; this collapses them
     * to newline-separated strings so no information is silently dropped.
     */
    private function flattenToString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Join array items with newlines to preserve all returned data.
        if (is_array($value)) {
            return implode("\n", array_map('strval', $value));
        }

        return (string) $value;
    }
}
