<?php

namespace App\Services;

use App\Models\Inception;
use Illuminate\Support\Facades\File;

class InceptionArtifactGenerator
{
    /**
     * Converts a database-backed Inception into local .md artifacts.
     */
    public function generate(Inception $inception): void
    {
        $project = $inception->project;

        // Determine target location. Fallback to storage app code folder if local_location is not set.
        $targetDirectory = $project->local_location ?: storage_path('app/projects/' . $project->project_uuid);
        
        $inceptionDir = $targetDirectory . '/inception';

        // Ensure directory exists
        if (!File::exists($inceptionDir)) {
            File::makeDirectory($inceptionDir, 0755, true);
        }

        $this->writeVision($inception, $inceptionDir);
        $this->writePersonas($inception, $inceptionDir);
        $this->writeFeatures($inception, $inceptionDir);
        $this->writeMVP($inception, $inceptionDir);
        
        $this->writeMasonDirective($inception, $inceptionDir);
    }

    private function writeVision(Inception $inception, string $dir): void
    {
        $content = "# Product Vision\n\n";
        
        if ($inception->vision_statement) {
            $content .= "## The Vision Statement\n";
            $content .= "> " . $inception->vision_statement . "\n\n";
        }

        if ($inception->business_goals) {
            $content .= "## Business Goals\n";
            $bg = is_array($inception->business_goals) ? implode("\n- ", $inception->business_goals) : (string) $inception->business_goals;
            $content .= $bg . "\n\n";
        }

        if ($inception->success_metrics) {
            $content .= "## Success Metrics\n";
            $sm = is_array($inception->success_metrics) ? implode("\n- ", $inception->success_metrics) : (string) $inception->success_metrics;
            $content .= $sm . "\n\n";
        }

        File::put($dir . '/vision.md', $content);
    }

    private function writePersonas(Inception $inception, string $dir): void
    {
        $content = "# Target Personas\n\n";

        foreach ($inception->personas as $persona) {
            $content .= "## " . $persona->name . "\n";
            if ($persona->tech_level) {
                $content .= "**Tech Level:** " . $persona->tech_level . "\n\n";
            }
            if ($persona->goals) {
                $content .= "### Goals\n" . $persona->goals . "\n\n";
            }
            if ($persona->frustrations) {
                $content .= "### Frustrations\n" . $persona->frustrations . "\n\n";
            }
            if ($persona->context) {
                $content .= "### Context\n" . $persona->context . "\n\n";
            }
            $content .= "---\n\n";
        }

        File::put($dir . '/personas.md', $content);
    }

    private function writeFeatures(Inception $inception, string $dir): void
    {
        $content = "# Brainstormed Features\n\n";
        $content .= "| Title | Value | Effort | Score | Description |\n";
        $content .= "|---|---|---|---|---|\n";

        foreach ($inception->features as $feature) {
            $score = number_format($feature->value_score / max(1, $feature->effort_score), 1);
            $desc = str_replace(["\r", "\n"], " ", (string)$feature->description);
            $content .= "| {$feature->title} | {$feature->value_score} | {$feature->effort_score} | {$score} | {$desc} |\n";
        }

        File::put($dir . '/features.md', $content);
    }

    private function writeMVP(Inception $inception, string $dir): void
    {
        $content = "# MVP Canvas (MoSCoW)\n\n";
        
        $features = $inception->features;
        $categories = [
            'Must Have' => 'Must Have (Core to the MVP)',
            'Should Have' => 'Should Have (Important, but not vital)',
            'Could Have' => 'Could Have (Nice to have)',
            'Won\'t Have' => 'Won\'t Have (Out of scope for this version)'
        ];

        foreach ($categories as $status => $title) {
            $content .= "## {$title}\n\n";
            $subset = $features->where('mvp_status', $status);
            
            if ($subset->isEmpty()) {
                $content .= "*None specified.*\n\n";
            } else {
                foreach ($subset as $f) {
                    $content .= "- **{$f->title}**\n";
                    if ($f->description) {
                        $content .= "  - {$f->description}\n";
                    }
                }
                $content .= "\n";
            }
        }

        File::put($dir . '/mvp.md', $content);
    }

    private function writeMasonDirective(Inception $inception, string $dir): void
    {
        $content = "# Mason Backlog Generation Directive\n\n";
        $content .= "SYSTEM: You are Mason, an expert agile architect. Your job is to translate this Lean Inception MVP into Epics, Stories, and Tasks.\n\n";
        
        $content .= "## Project Vision\n";
        $content .= $inception->vision_statement . "\n\n";

        $content .= "## The 'Must Have' MVP Checklist\n";
        $subset = $inception->features->where('mvp_status', 'Must Have');
        foreach ($subset as $f) {
            $content .= "- [ ] {$f->title}: {$f->description}\n";
        }
        $content .= "\n";
        $content .= "INSTRUCTIONS: For each item in the checklist above, generate a robust User Story and list the technical Tasks required to implement it.\n";

        File::put($dir . '/mason_directive.md', $content);
    }
}
