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

        // Build the HTML once so rendering stays fast.
        $this->conversationHtml = $this->buildConversationHtml($conversation->raw_content);
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
     * Render the transcript view.
     */
    public function render()
    {
        return view('livewire.conversation-transcript-page')
            ->layout('layouts.app');
    }
}
