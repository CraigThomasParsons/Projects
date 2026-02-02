<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Extracts markdown checkbox lines without mutating original content.
 */
final class MessageCheckboxExtractor
{
    /**
     * Parse markdown and return checkbox metadata in original order.
     *
     * @return array<int, array{position_index:int,label:string,is_checked_in_markdown:bool}>
     */
    public function extractCheckboxLines(string $markdownContent): array
    {
        // Normalize the content into individual lines for reliable parsing.
        $markdownLines = preg_split('/\R/', $markdownContent);

        // Guard against unexpected splitting failures to keep parsing safe.
        if ($markdownLines === false) {
            return [];
        }

        $checkboxes = [];
        $positionIndex = 0;

        foreach ($markdownLines as $lineText) {
            // Match GitHub-style checkbox syntax like "- [ ] Task".
            $matchesCheckbox = preg_match(
                '/^\s*[-*]\s+\[( |x|X)\]\s+(.*)$/',
                $lineText,
                $matches
            );

            // Skip any line that does not represent a checkbox.
            if ($matchesCheckbox !== 1) {
                continue;
            }

            $checkboxes[] = [
                'position_index' => $positionIndex,
                'label' => trim($matches[2]),
                'is_checked_in_markdown' => strtolower($matches[1]) === 'x',
            ];

            // Advance the position index only for valid checkbox lines.
            $positionIndex++;
        }

        return $checkboxes;
    }

    /**
     * Remove checkbox lines so the rendered markdown stays clean.
     */
    public function stripCheckboxLines(string $markdownContent): string
    {
        // Normalize the content into individual lines for filtering.
        $markdownLines = preg_split('/\R/', $markdownContent);

        // Guard against unexpected splitting failures to keep rendering safe.
        if ($markdownLines === false) {
            return $markdownContent;
        }

        $filteredLines = [];

        foreach ($markdownLines as $lineText) {
            // Skip checkbox lines so checklists render in a dedicated section.
            if (preg_match('/^\s*[-*]\s+\[( |x|X)\]\s+.*$/', $lineText) === 1) {
                continue;
            }

            $filteredLines[] = $lineText;
        }

        return implode("\n", $filteredLines);
    }
}
