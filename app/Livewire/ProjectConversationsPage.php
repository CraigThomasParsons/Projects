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

    public bool $showEditProjectForm = false;
    public string $editProjectName = '';
    public string $editProjectDescription = '';
    public string $editLocalLocation = '';
    public string $editGithubRepo = '';
    public string $editGiteaLocation = '';
    public string $editFrameworkDescription = '';
    public string $editLanguages = '';

    public string $manualConversationTitle = '';
    public string $manualConversationContent = '';

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
     * Show the edit project form with current values.
     */
    public function openEditProjectForm(): void
    {
        $this->editProjectName = $this->project->name;
        $this->editProjectDescription = $this->project->description ?? '';
        $this->editLocalLocation = $this->project->local_location ?? '';
        $this->editGithubRepo = $this->project->github_repo ?? '';
        $this->editGiteaLocation = $this->project->gitea_location ?? '';
        $this->editFrameworkDescription = $this->project->framework_description ?? '';
        $this->editLanguages = $this->project->languages ?? '';
        $this->showEditProjectForm = true;
    }

    /**
     * Close the edit project form.
     */
    public function closeEditProjectForm(): void
    {
        $this->showEditProjectForm = false;
    }

    /**
     * Persist edits to the current project.
     */
    public function updateProject(): void
    {
        $validatedData = $this->validate([
            'editProjectName' => ['required', 'string', 'max:255'],
            'editProjectDescription' => ['nullable', 'string'],
            'editLocalLocation' => ['nullable', 'string', 'max:500'],
            'editGithubRepo' => ['nullable', 'string', 'max:500'],
            'editGiteaLocation' => ['nullable', 'string', 'max:500'],
            'editFrameworkDescription' => ['nullable', 'string'],
            'editLanguages' => ['nullable', 'string'],
        ]);

        $this->project->update([
            'name' => $validatedData['editProjectName'],
            'description' => $validatedData['editProjectDescription'],
            'local_location' => $validatedData['editLocalLocation'],
            'github_repo' => $validatedData['editGithubRepo'],
            'gitea_location' => $validatedData['editGiteaLocation'],
            'framework_description' => $validatedData['editFrameworkDescription'],
            'languages' => $validatedData['editLanguages'],
        ]);

        $this->project->refresh();
        $this->showEditProjectForm = false;

        session()->flash('success', 'Project updated.');
    }

    /**
     * Delete the current project and its conversations.
     */
    public function deleteProject()
    {
        $projectName = $this->project->name;
        $this->project->delete();

        session()->flash('success', "Project '{$projectName}' deleted.");

        return redirect()->route('projects.index');
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
            'source_type' => 'share_url',
        ]);

        // Refresh the list and reset the form for the next entry.
        $this->loadConversations();
        $this->shareUrl = '';
        $this->closeConversationModal();

        session()->flash('success', 'Conversation saved. Use Sync to import.');
    }

    /**
     * Persist a manually pasted conversation transcript.
     */
    public function savePastedConversation(): void
    {
        $validatedData = $this->validate([
            'manualConversationTitle' => ['nullable', 'string', 'max:255'],
            'manualConversationContent' => ['required', 'string', 'min:20'],
        ]);

        Conversation::create([
            'project_id' => $this->project->id,
            'title' => $validatedData['manualConversationTitle'] ?: 'Pasted Conversation',
            'share_url' => 'manual://paste',
            'source_type' => 'manual_paste',
            'raw_content' => $validatedData['manualConversationContent'],
        ]);

        $this->loadConversations();
        $this->manualConversationTitle = '';
        $this->manualConversationContent = '';
        $this->closeConversationModal();

        session()->flash('success', 'Pasted conversation saved.');
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
     * Delete a conversation from the current project.
     */
    public function deleteConversation(int $conversationId): void
    {
        $conversation = $this->project->conversations()->find($conversationId);
        if ($conversation === null) {
            session()->flash('error', 'Conversation not found for this project.');
            return;
        }

        $conversation->delete();
        $this->loadConversations();

        session()->flash('success', 'Conversation deleted.');
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
