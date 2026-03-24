<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Project;
use App\Services\ChatGptShareImporter;
use App\Services\PiperBrowserConversationSyncService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

    const CHAT_LINK = 'chat_link';
    const DEFAULT_THEME = 'cyberpunk';

    public Project $project;
    public Collection $conversations;

    public string $theme = self::DEFAULT_THEME;

    public bool $showShareUrlModal = false;
    public bool $showChatLinkModal = false;
    public bool $showManualPasteModal = false;
    public string $shareUrl = '';
    public string $chatConversationUrl = '';

    public bool $showEditProjectForm = false;
    public string $editProjectName = '';
    public string $editProjectType = 'code';
    public string $editProjectDescription = '';
    public string $editLocalLocation = '';
    public string $editGithubRepo = '';
    public string $editGiteaLocation = '';
    public string $editFrameworkDescription = '';
    public string $editLanguages = '';

    public string $manualConversationTitle = '';
    public string $manualConversationContent = '';

    // The bad practice inline styles.
    public string $wrongWayTodoThings = '';
    public string $callOutSecondaryStyle = '';
    public string $pageHeaderOverrideStyle = '';
    public string $pageHeaderClasses = 'button hollow secondary';

    public array $callOutSecondaryStyles = [
        'margin-top: 0.75rem;'
    ];

    public bool $showMoveModal = false;
    public ?int $conversationToMoveId = null;
    public ?int $targetProjectId = null;
    public Collection $availableProjects;

    /**
     * Initialize the component with the target project.
     */
    public function mount(Project $project): void
    {
        // Cache the route-bound project for reuse.
        $this->project = $project;

        // Read theme from cookie so server-side logic can be theme-aware on initial load.
        $this->theme = request()->cookie('theme', 'cyberpunk');

        // Load the initial conversations so the list renders immediately.
        $this->loadConversations();

        // Too much vibecoding, had to start nailing things to wall to get them to change.
        $this->initializeProjectStyles();
    }

    /**
     * Initialize the component with custom styles for the edit project form.
     * Too much vibecoding led to this. But finally I'm going to 
     */
public function initializeProjectStyles(): void
{
    $inlineStyle = [
        'width: 80vw',
        'max-width: none',
        'min-width: 80vw',
        'background-color: #23202e',
        'color: #d0d0d0',
        'position: relative'
    ];

    // Fix bracket structure and logic
    if ($this->theme === 'foundation') {
        // Override styles for Foundation theme to fix modal conflicts.
        $inlineStyle[] = 'color: #fefefe';
        $inlineStyle[] = 'background-color: #334155';
        $inlineStyle[] = 'left: 50%';
        $inlineStyle[] = 'transform: translateX(-50%)';

        $this->callOutSecondaryStyles[] = 'color: #d0d0d0';
        $this->callOutSecondaryStyles[] = 'background-color: #334155';

    } elseif ($this->theme === 'lcars') {
        $inlineStyle[] = 'background-color: #000000';
        $inlineStyle[] = 'color: #ff9900';
    }

    if ($this->theme === 'writers-room') {
        $inlineStyle[] = 'background-color: #fefefe';
        $inlineStyle[] = 'color: #23202e';
    } else {
        $this->pageHeaderClasses .= ' small ';
    }

    // This is a "wrong way todo thing" to inject styles for the edit project form without a separate CSS file.
    // The styles are necessary to override Foundation's default modal styles which are applied via class and interfere with our custom layout.
    $this->wrongWayTodoThings = implode('; ', $inlineStyle);
    $this->callOutSecondaryStyle = implode('; ', $this->callOutSecondaryStyles);
    $this->pageHeaderOverrideStyle = implode('; ', [
        'border-bottom: 1px solid #444;',
        'margin-bottom: 1rem;',
    ]);
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
     * Open the new conversation modal.
     */
    public function openShareUrlModal(): void
    {
        // Keep only one modal open at a time to avoid overlapping overlays.
        $this->closeConversationModals();
        $this->showShareUrlModal = true;
    }

    /**
     * Open the chat-link modal for Piper browser extraction flow.
     */
    public function openChatLinkModal(): void
    {
        // Keep only one modal open at a time to avoid overlapping overlays.
        $this->closeConversationModals();
        $this->showChatLinkModal = true;
    }

    /**
     * Open the manual paste modal.
     */
    public function openManualPasteModal(): void
    {
        // Keep only one modal open at a time to avoid overlapping overlays.
        $this->closeConversationModals();
        $this->showManualPasteModal = true;
    }

    /**
     * Close all conversation creation modals.
     */
    public function closeConversationModals(): void
    {
        // Hide every creation overlay so UI state is deterministic.
        $this->showShareUrlModal = false;
        $this->showChatLinkModal = false;
        $this->showManualPasteModal = false;
        $this->showMoveModal = false;
        $this->conversationToMoveId = null;
        $this->targetProjectId = null;
    }

    /**
     * Open the move conversation modal.
     */
    public function openMoveModal(int $conversationId): void
    {
        $this->closeConversationModals();
        $this->conversationToMoveId = $conversationId;
        $this->availableProjects = Project::where('id', '!=', $this->project->id)->orderBy('name')->get();
        
        // Default to Unassigned if it exists
        $unassigned = $this->availableProjects->firstWhere('name', 'Unassigned');
        if ($unassigned) {
            $this->targetProjectId = $unassigned->id;
        }

        $this->showMoveModal = true;
    }

    /**
     * Close the move modal.
     */
    public function closeMoveModal(): void
    {
        $this->showMoveModal = false;
        $this->conversationToMoveId = null;
        $this->targetProjectId = null;
    }

    /**
     * Move the conversation to a new project, physically relocating the file if it exists.
     */
    public function moveConversation(): void
    {
        $this->validate([
            'conversationToMoveId' => 'required|exists:conversations,id',
            'targetProjectId' => 'required|exists:projects,id',
        ]);

        $conversation = $this->project->conversations()->find($this->conversationToMoveId);
        $targetProject = Project::find($this->targetProjectId);

        if (!$conversation || !$targetProject) {
            session()->flash('error', 'Unable to complete move. Invalid conversation or project.');
            $this->closeMoveModal();
            return;
        }

        $title = $conversation->title ?? 'Imported Conversation';
        $sanitizedTitle = Str::slug($title);

        $oldSanitizedProjectName = Str::slug($this->project->name);
        $newSanitizedProjectName = Str::slug($targetProject->name);

        $oldFilePath = base_path("Projects/{$oldSanitizedProjectName}/{$sanitizedTitle}.md");
        $newDirPath = base_path("Projects/{$newSanitizedProjectName}");
        $newFilePath = "{$newDirPath}/{$sanitizedTitle}.md";

        if (File::exists($oldFilePath)) {
            if (!File::exists($newDirPath)) {
                File::makeDirectory($newDirPath, 0755, true);
            }
            File::move($oldFilePath, $newFilePath);
        }

        $conversation->update(['project_id' => $targetProject->id]);

        session()->flash('success', "Conversation moved to {$targetProject->name}.");
        $this->loadConversations();
        $this->closeMoveModal();
    }

    /**
     * Show the edit project form with current values.
     */
    public function openEditProjectForm(): void
    {
        $this->editProjectName = $this->project->name;
        $this->editProjectType = $this->project->type ?? 'code';
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
            'editProjectType' => ['required', 'in:code,idea'],
            'editProjectDescription' => ['nullable', 'string'],
            'editLocalLocation' => ['nullable', 'string', 'max:500'],
            'editGithubRepo' => ['nullable', 'string', 'max:500'],
            'editGiteaLocation' => ['nullable', 'string', 'max:500'],
            'editFrameworkDescription' => ['nullable', 'string'],
            'editLanguages' => ['nullable', 'string'],
        ]);

        $this->project->update([
            'name' => $validatedData['editProjectName'],
            'type' => $validatedData['editProjectType'],
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
        $this->closeConversationModals();

        session()->flash('success', 'Conversation saved. Use Sync to import.');
    }

    /**
     * Persist a non-share ChatGPT conversation URL for Piper browser extraction.
     */
    public function saveChatConversationLink(): void
    {
        // Validate private conversation links produced by ChatGPT chat sessions.
        $validatedData = $this->validate([
            'chatConversationUrl' => [
                'required',
                'url',
                'regex:/^https:\/\/chatgpt\.com\/(?:g\/g-[^\/]+\/c\/[a-z0-9\-]+|c\/[a-z0-9\-]+)/i',
            ],
        ]);

        // Save as a separate source type so sync can route to Piper browser extraction.
        Conversation::create([
            'project_id' => $this->project->id,
            'share_url' => $validatedData['chatConversationUrl'],
            'source_type' => self::CHAT_LINK
        ]);

        // Refresh list and clear entry form for next conversation.
        $this->loadConversations();
        $this->chatConversationUrl = '';
        $this->closeConversationModals();

        session()->flash('success', 'Conversation link saved. Use Sync to run Piper browser extraction.');
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
        $this->closeConversationModals();

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
            // Route sync behavior by source type to keep extraction explicit.
            if ($conversation->source_type === self::CHAT_LINK) {
                app(PiperBrowserConversationSyncService::class)->queueExtraction($conversation);
                session()->flash('success', 'Piper browser extraction job queued.');
            } else {
                // Default behavior for public share links.
                app(ChatGptShareImporter::class)->import($conversation, $conversation->share_url);
                session()->flash('success', 'Conversation synced.');
            }
        } catch (Throwable $exception) {
            // Keep the record but surface the failure so the user can retry.
            report($exception);
            session()->flash('error', 'Failed to sync conversation: ' . $exception->getMessage());
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
