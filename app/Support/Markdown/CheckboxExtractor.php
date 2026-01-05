<?php

declare(strict_types=1);

namespace App\Support\Markdown;

final class CheckboxExtractor
{
    /**
     * Extract GitHub-style markdown checkboxes from text.
     *
     * Returns an ordered list suitable for persistence.
     *
     * Each checkbox includes:
     * - index: stable position within the message
     * - label: rendered text
     * - checked: boolean
     */
    public static function extract(string $markdown): array
    {
        $lines = preg_split('/\R/', $markdown);
        $checkboxes = [];
        $index = 0;

        foreach ($lines as $line) {
            // Match:
            // - [ ] Label
            // - [x] Label
            // * [ ] Label
            if (preg_match('/^\s*[-*]\s+\[( |x|X)\]\s+(.*)$/', $line, $matches)) {
                $checkboxes[] = [
                    'index'   => $index,
                    'label'   => trim($matches[2]),
                    'checked' => strtolower($matches[1]) === 'x',
                ];

                $index++;
            }
        }

        return $checkboxes;
    }
}
