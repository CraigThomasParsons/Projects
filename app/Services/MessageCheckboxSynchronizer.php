<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Message;

/**
 * Keeps stored checkbox state in sync with parsed markdown structure.
 */
final class MessageCheckboxSynchronizer
{
    /**
     * Sync checkbox rows for a message using extracted markdown metadata.
     *
     * @param array<int, array{position_index:int,label:string,is_checked_in_markdown:bool}> $extractedCheckboxes
     */
    public function syncMessageCheckboxes(Message $message, array $extractedCheckboxes): void
    {
        // Load existing checkboxes so we can preserve user toggle state.
        $existingCheckboxes = $message->checkboxes()->get()->keyBy('position_index');

        $seenPositionIndexes = [];

        foreach ($extractedCheckboxes as $checkboxData) {
            $positionIndex = $checkboxData['position_index'];
            $seenPositionIndexes[] = $positionIndex;

            $existingCheckbox = $existingCheckboxes->get($positionIndex);

            if ($existingCheckbox !== null) {
                // Update the label when the markdown text changes.
                if ($existingCheckbox->label !== $checkboxData['label']) {
                    $existingCheckbox->label = $checkboxData['label'];
                    $existingCheckbox->save();
                }

                // Preserve existing is_checked state to honor user toggles.
                continue;
            }

            // Create a new checkbox entry when the markdown introduces one.
            $message->checkboxes()->create([
                'position_index' => $positionIndex,
                'label' => $checkboxData['label'],
                'is_checked' => $checkboxData['is_checked_in_markdown'],
            ]);
        }

        // Remove checkboxes that no longer exist in the markdown.
        if (!empty($seenPositionIndexes)) {
            $message->checkboxes()
                ->whereNotIn('position_index', $seenPositionIndexes)
                ->delete();
        }

        // Clear all checkboxes if none remain in the markdown.
        if (empty($seenPositionIndexes)) {
            $message->checkboxes()->delete();
        }
    }
}
