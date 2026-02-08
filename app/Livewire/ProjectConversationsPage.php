<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Project;
use App\Services\ChatGptShareImporter;
use Illuminate\Support\Collection;
use Livewire\Component;
use Throwable;

/**
 * Displays conversations for a single project and manages syncs.
 *
 * This component is responsible for:
 * - Showing project metadata
 * - Creating placeholder conversations from ChatGPT share URLs
 * - Triggering sync to import conversation content on demand
 */
class ProjectConversationsPage extends Component
{
    public Project $project;
    public Collection $conversations;

    public bool $showConversationModal = false;
    public string $shareUrl = '';

    /**
     * Initialize the component with the target project.
     */
    public function mount(Project $project): void
    {
        // Cache the route-bound project for reuse.
        $this->project = $project;

        // Load the initial conversations so the list renders immediately.
        $this->loadConversations();
    }

    /**
     * Open the new conversation modal.
     */
    public function openConversationModal(): void
    {
        // Toggle modal visibility so the overlay appears.
        $this->showConversationModal = true;
    }

    /**
     * Close the new conversation modal.
     */
    public function closeConversationModal(): void
    {
        // Hide the modal overlay.
        $this->showConversationModal = false;
    }

    /**
     * Persist a conversation shell without importing content.
     */
    public function saveConversation(): void
    {
        // Validate the share URL format before persisting.
        $validatedData = $this->validate([
            'shareUrl' => ['required', 'url', 'regex:/^https:\/\/chatgpt\.com\/share\/.+/'],
        ]);

        // Create the conversation with only metadata; syncing is deferred.
        Conversation::create([
            'project_id' => $this->project->id,
            'share_url' => $validatedData['shareUrl'],
        ]);

        // Refresh the list and reset the form for the next entry.
        $this->loadConversations();
        $this->shareUrl = '';
        $this->closeConversationModal();

        session()->flash('success', 'Conversation saved. Use Sync to import.');
    }

    /**
     * Sync a conversation by importing its ChatGPT content.
     */
    public function syncConversation(int $conversationId): void
    {
        // Ensure the conversation belongs to the current project.
        $conversation = $this->project->conversations()->find($conversationId);
        if ($conversation === null) {
            session()->flash('error', 'Conversation not found for this project.');
            return;
        }

        try {
            // Pull the latest content from the share URL.
            app(ChatGptShareImporter::class)->import($conversation, $conversation->share_url);
            session()->flash('success', 'Conversation synced.');
        } catch (Throwable $exception) {
            // Keep the record but surface the failure so the user can retry.
            report($exception);
            session()->flash('error', 'Failed to sync conversation.');
        }

        // Refresh the list to reflect any imported metadata.
        $this->loadConversations();
    }

    /**
     * Refresh the conversation collection for this project.
     */
    private function loadConversations(): void
    {
        // Order newest-first so recent conversations are easy to find.
        $this->conversations = $this->project
            ->conversations()
            ->latest()
            ->get();
    }

    /**
     * Render the component view.
     */
    public function render()
    {
        return view('livewire.project-conversations-page')
            ->layout('layouts.app');
    }
}
