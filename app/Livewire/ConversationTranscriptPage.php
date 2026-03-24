<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Project;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Displays a single conversation transcript in a scrollable view.
 *
 * This component is responsible for:
 * - Ensuring the conversation belongs to the project
 * - Rendering the markdown transcript as HTML
 * - Providing navigation back to the project conversations list
 */
class ConversationTranscriptPage extends Component
{
    public Project $project;
    public Conversation $conversation;

    public string $conversationHtml = '';
    public string $newEntry = '';
    public string $editableTranscript = '';
    public string $theme = 'cyberpunk';
    public string $pageHeaderClasses = 'button hollow secondary';
    public string $callOutStyle = '';

    /**
     * Initialize the transcript view with the requested project and conversation.
     */
    public function mount(Project $project, Conversation $conversation): void
    {
        // Guard against mismatched routes to avoid leaking other projects.
        if ($conversation->project_id !== $project->id) {
            abort(404);
        }

        // Cache the route-bound models for rendering.
        $this->project = $project;
        $this->conversation = $conversation;

        // Read theme from cookie so server-side logic can be theme-aware on initial load.
        $this->theme = request()->cookie('theme', 'cyberpunk');

        // Build the HTML once so rendering stays fast.
        $this->conversationHtml = $this->buildConversationHtml($conversation->raw_content);

        // Initialize editable text area with the current stored transcript.
        $this->editableTranscript = (string) ($conversation->raw_content ?? '');

        // Apply theme-specific styles for the callout sections.
        $this->callOutStyle = $this->initializeCalloutStyles();

        if ($this->theme !== 'writers-room') {
            $this->pageHeaderClasses .= ' small ';
        }
    }

    /**
     * Determine CSS styles for callout sections based on the active theme.
     *
     * @return string $stylespace-separated list of CSS styles to apply to callout sections
     */
    protected function initializeCalloutStyles(): string
    {
        // Apply theme-specific styles for the callout sections.
        $callOutStyles = ['margin-top: 0.75rem;'];
    
        // Seems I built this exclusively for the foundation theme, but it's possible other themes could use it too so I'll keep it flexible.
        if ($this->theme === 'foundation') {
            $callOutStyles[] = ' background-color: #334155;';
        }

        return implode('; ', $callOutStyles);
    }

    /**
     * Convert raw markdown into HTML for display.
     */
    private function buildConversationHtml(?string $rawContent): string
    {
        // Provide a friendly fallback when no transcript is available.
        if ($rawContent === null || trim($rawContent) === '') {
            return '<p class="text-muted">No transcript yet. Use Sync to import the conversation.</p>';
        }

        // Render markdown so the transcript reads cleanly.
        return Str::markdown($rawContent);
    }

    /**
     * Append user-provided text to the current conversation transcript.
     */
    public function addToConversation(): void
    {
        // Validate non-empty input before mutating the transcript.
        $validated = $this->validate([
            'newEntry' => ['required', 'string', 'min:2'],
        ]);

        // Keep existing transcript intact and append the new entry as markdown.
        $existingContent = trim((string) $this->conversation->raw_content);
        $entryContent = trim($validated['newEntry']);

        // Include a timestamp marker so appended content remains auditable.
        $timestampHeading = '### Manual Note (' . now()->toDateTimeString() . ')';
        $appendedBlock = $timestampHeading . "\n\n" . $entryContent;

        // Preserve readability with paragraph spacing between transcript blocks.
        $updatedContent = $existingContent === ''
            ? $appendedBlock
            : $existingContent . "\n\n---\n\n" . $appendedBlock;

        // Persist the updated transcript and refresh rendered HTML.
        $this->conversation->update([
            'raw_content' => $updatedContent,
        ]);

        // Re-render from updated content so the page reflects the new entry immediately.
        $this->conversationHtml = $this->buildConversationHtml($updatedContent);

        // Clear input for the next note.
        $this->newEntry = '';

        session()->flash('success', 'Conversation updated.');
    }

    /**
     * Save direct edits to the full conversation transcript.
     */
    public function saveTranscriptEdits(): void
    {
        // Validate that edited transcript content is present.
        $validated = $this->validate([
            'editableTranscript' => ['required', 'string', 'min:2'],
        ]);

        // Persist the full transcript replacement as authored by the user.
        $updatedContent = trim($validated['editableTranscript']);

        $this->conversation->update([
            'raw_content' => $updatedContent,
        ]);

        // Rebuild rendered markdown so the page reflects edits immediately.
        $this->conversationHtml = $this->buildConversationHtml($updatedContent);

        session()->flash('success', 'Conversation transcript saved.');
    }

    /**
     * Sync the active theme from the client after a live theme switch.
     */
    public function setTheme(string $theme): void
    {
        $allowed = ['cyberpunk', 'foundation', 'lcars', 'writers-room'];
        $this->theme = in_array($theme, $allowed, true) ? $theme : 'cyberpunk';
    }

    /**
     * Render the transcript view.
     */
    public function render()
    {
        return view('livewire.conversation-transcript-page')
            ->layout('layouts.app');
    }
}
