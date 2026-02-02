<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageCheckbox;
use App\Models\Project;
use App\Services\MessageCheckboxExtractor;
use App\Services\MessageCheckboxSynchronizer;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Renders the core chat-first workflow with projects and conversations.
 */
final class ChatPanel extends Component
{
	/**
	 * @var array<int, array<string, mixed>>
	 */
	public array $projectList = [];

	/**
	 * @var array<int, array<string, mixed>>
	 */
	public array $conversationList = [];

	/**
	 * @var array<int, array<string, mixed>>
	 */
	public array $messageList = [];

	public ?int $selectedProjectId = null;

	public ?int $selectedConversationId = null;

	public string $newProjectName = '';

	public string $newConversationTitle = '';

	public string $newMessageContent = '';
	public bool $showNewProjectForm = false;
	public bool $showNewConversationForm = false;
	public string $shareUrl = '';
	public string $originalUrl = '';

	/**
	 * Initialize the component with a sensible default project and conversation.
	 */
	public function mount(): void
	{
		// Ensure there is always a safe container to capture new ideas.
		$this->ensureDefaultProjectAndConversation();

		// Load UI lists after defaults are in place.
		$this->loadProjects();
		$this->loadConversations();
		$this->loadMessages();
	}

	/**
	 * Select a project and reload dependent lists.
	 */
	public function selectProject(int $projectId): void
	{
		// Guard against selecting a missing project to avoid null cascades.
		if (!Project::query()->whereKey($projectId)->exists()) {
			return;
		}

		$this->selectedProjectId = $projectId;
		$this->selectedConversationId = null;

		// Refresh dependent lists so the UI stays consistent.
		$this->loadConversations();
		$this->loadMessages();
	}

	/**
	 * Select a conversation and reload messages.
	 */
	public function selectConversation(int $conversationId): void
	{
		// Guard against selecting a missing conversation to avoid null cascades.
		if (!Conversation::query()->whereKey($conversationId)->exists()) {
			return;
		}

		$this->selectedConversationId = $conversationId;

		$conversation = Conversation::find($conversationId);
		$this->shareUrl = $conversation?->share_url ?? '';
		$this->originalUrl = $conversation?->original_url ?? '';

		// Refresh message list to show the selected conversation thread.
		$this->loadMessages();
	}

	/**
	 * Create a new project and select it immediately.
	 */
	public function createProject(): void
	{
		// Validate the project name so we never create blank containers.
		$validatedData = $this->validate([
			'newProjectName' => ['required', 'string', 'min:2', 'max:120'],
		]);

		$project = Project::query()->create([
			'name' => $validatedData['newProjectName'],
			'status' => 'active',
			'last_activity_at' => now(),
		]);

		// Reset input immediately to keep the UI calm and predictable.
		$this->newProjectName = '';
		$this->showNewProjectForm = false;

		$this->selectedProjectId = $project->id;

		// Ensure every project starts with a default conversation.
		$this->createInitialConversationForProject($project);

		// Refresh lists so the new project appears instantly.
		$this->loadProjects();
		$this->loadConversations();
		$this->loadMessages();
	}

	/**
	 * Create a new conversation in the selected project.
	 */
	public function createConversation(): void
	{
		// Guard against missing project context to avoid orphaned rows.
		if ($this->selectedProjectId === null) {
			return;
		}

		// Validate the conversation title so navigation stays meaningful.
		$validatedData = $this->validate([
			'newConversationTitle' => ['required', 'string', 'min:2', 'max:160'],
		]);

		$conversation = Conversation::query()->create([
			'project_id' => $this->selectedProjectId,
			'title' => $validatedData['newConversationTitle'],
			'status' => 'active',
		]);

		// Reset input to keep the capture flow fast.
		$this->newConversationTitle = '';
		$this->showNewConversationForm = false;

		$this->selectedConversationId = $conversation->id;

		// Refresh lists so the new conversation appears instantly.
		$this->loadConversations();
		$this->loadMessages();
	}

	/**
	 * Persist a new message and sync checkbox state.
	 */
	public function sendMessage(): void
	{
		// Guard against missing conversation context to avoid orphaned messages.
		if ($this->selectedConversationId === null) {
			return;
		}

		// Validate content to prevent empty or whitespace-only messages.
		$validatedData = $this->validate([
			'newMessageContent' => ['required', 'string', 'min:1'],
		]);

		$message = Message::query()->create([
			'conversation_id' => $this->selectedConversationId,
			'author_role' => 'user',
			'content' => $validatedData['newMessageContent'],
		]);

		// Clear the input to keep the typing loop smooth.
		$this->newMessageContent = '';

		// Extract and sync checkbox state without mutating markdown content.
		$checkboxExtractor = app(MessageCheckboxExtractor::class);
		$checkboxSynchronizer = app(MessageCheckboxSynchronizer::class);

		$extractedCheckboxes = $checkboxExtractor->extractCheckboxLines($message->content);
		$checkboxSynchronizer->syncMessageCheckboxes($message, $extractedCheckboxes);

		// Update resume hints on the conversation and project.
		$this->touchConversationAndProject($message->conversation_id);

		// Reload messages so the UI shows the new entry immediately.
		$this->loadMessages();

		// Dispatch event to Alpine to signal message sent (to trigger the bridge)
		$this->dispatch('messageSent', content: $message->content);
	}

	/**
	 * Save an AI response received via the bridge.
	 */
	public function saveAiResponse(string $content): void
	{
		if ($this->selectedConversationId === null) {
			return;
		}

		$message = Message::query()->create([
			'conversation_id' => $this->selectedConversationId,
			'author_role' => 'assistant',
			'content' => $content,
		]);

		// Sync checkboxes if any
		$checkboxExtractor = app(MessageCheckboxExtractor::class);
		$checkboxSynchronizer = app(MessageCheckboxSynchronizer::class);
		$extractedCheckboxes = $checkboxExtractor->extractCheckboxLines($message->content);
		$checkboxSynchronizer->syncMessageCheckboxes($message, $extractedCheckboxes);

		$this->touchConversationAndProject($message->conversation_id);
		$this->loadMessages();
	}

	/**
	 * Toggle a stored checkbox without changing markdown content.
	 */
	public function toggleCheckbox(int $checkboxId): void
	{
		// Guard against missing checkbox records to avoid errors.
		$checkbox = MessageCheckbox::query()->find($checkboxId);

		if ($checkbox === null) {
			return;
		}

		$checkbox->is_checked = !$checkbox->is_checked;
		$checkbox->save();

		// Reload messages so the UI reflects the updated state.
		$this->loadMessages();
	}

	/**
	 * Build the message list for the current conversation.
	 */
	private function loadMessages(): void
	{
		// Guard against missing conversation context to avoid wasted queries.
		if ($this->selectedConversationId === null) {
			$this->messageList = [];
			return;
		}

		$checkboxExtractor = app(MessageCheckboxExtractor::class);

		$messages = Message::query()
			->with('checkboxes')
			->where('conversation_id', $this->selectedConversationId)
			->orderBy('created_at')
			->get();

		$this->messageList = $messages->map(function (Message $message) use ($checkboxExtractor) {
			// Strip checkbox lines to avoid duplicate rendering.
			$markdownWithoutCheckboxes = $checkboxExtractor->stripCheckboxLines($message->content);

			// Render markdown to HTML for readable message display.
			$renderedHtml = Str::markdown($markdownWithoutCheckboxes);

			return [
				'id' => $message->id,
				'author_role' => $message->author_role,
				'created_at' => $message->created_at?->toDateTimeString(),
				'rendered_html' => $renderedHtml,
				'checkboxes' => $message->checkboxes->map(function (MessageCheckbox $checkbox) {
					return [
						'id' => $checkbox->id,
						'label' => $checkbox->label,
						'is_checked' => $checkbox->is_checked,
					];
				})->all(),
			];
		})->all();
	}

	/**
	 * Build the project list for the sidebar.
	 */
	private function loadProjects(): void
	{
		$projects = Project::query()
			->orderByDesc('last_activity_at')
			->orderByDesc('created_at')
			->get();

		$this->projectList = $projects->map(function (Project $project) {
			return [
				'id' => $project->id,
				'name' => $project->name,
				'status' => $project->status,
			];
		})->all();

		// Auto-select the first project when none is selected yet.
		if ($this->selectedProjectId === null && $projects->isNotEmpty()) {
			$this->selectedProjectId = $projects->first()->id;
		}
	}

	/**
	 * Build the conversation list for the selected project.
	 */
	private function loadConversations(): void
	{
		// Guard against missing project context to avoid wasted queries.
		if ($this->selectedProjectId === null) {
			$this->conversationList = [];
			return;
		}

		$conversations = Conversation::query()
			->where('project_id', $this->selectedProjectId)
			->orderByDesc('last_message_at')
			->orderByDesc('created_at')
			->get();

		$this->conversationList = $conversations->map(function (Conversation $conversation) {
			return [
				'id' => $conversation->id,
				'title' => $conversation->title,
				'status' => $conversation->status,
			];
		})->all();

		// Auto-select the first conversation when none is selected yet.
		if ($this->selectedConversationId === null && $conversations->isNotEmpty()) {
			$this->selectedConversationId = $conversations->first()->id;
		}
	}

	/**
	 * Ensure the first-run experience always has a safe capture space.
	 */
	private function ensureDefaultProjectAndConversation(): void
	{
		// Short-circuit when projects already exist.
		if (Project::query()->exists()) {
			return;
		}

		$project = Project::query()->create([
			'name' => 'Inbox',
			'description' => 'A safe place to drop ideas before organizing them.',
			'status' => 'active',
			'last_activity_at' => now(),
		]);

		$this->createInitialConversationForProject($project);
	}

	/**
	 * Create a default conversation for a new project.
	 */
	private function createInitialConversationForProject(Project $project): void
	{
		// Avoid duplicate defaults when a project already has conversations.
		if ($project->conversations()->exists()) {
			return;
		}

		$conversation = Conversation::query()->create([
			'project_id' => $project->id,
			'title' => 'General',
			'status' => 'active',
		]);

		$this->selectedConversationId = $conversation->id;
	}

	/**
	 * Update timestamps that support resume and sorting behavior.
	 */
	private function touchConversationAndProject(int $conversationId): void
	{
		$conversation = Conversation::query()->find($conversationId);

		// Guard against missing conversation to avoid null updates.
		if ($conversation === null) {
			return;
		}

		$conversation->last_message_at = now();
		$conversation->save();

		$project = $conversation->project;

		// Guard against missing project relationships to avoid null updates.
		if ($project === null) {
			return;
		}

		$project->last_activity_at = now();
		$project->save();
	}

	/**
	 * Render the chat panel view.
	 */
    /**
     * Save the shared conversation URL and trigger Piper integration.
     */
    /**
     * Save conversation references and trigger Piper.
     */
    public function saveShareUrl(): void
    {
        if ($this->selectedConversationId === null) {
            return;
        }

        $this->validate([
            'shareUrl' => ['nullable', 'url', 'regex:/^https:\/\/chatgpt\.com\/share\/.+/'],
            'originalUrl' => ['nullable', 'url', 'regex:/^https:\/\/chatgpt\.com\/c\/[a-f0-9-]+/'],
        ]);

        $conversation = Conversation::find($this->selectedConversationId);
        if ($conversation) {
            // Extract UUID from original URL
            $chatgptId = null;
            if (preg_match('/\/c\/([a-f0-9-]+)/', $this->originalUrl, $matches)) {
                $chatgptId = $matches[1];
            }

            $conversation->update([
                'share_url' => $this->shareUrl,
                'original_url' => $this->originalUrl,
                'chatgpt_id' => $chatgptId,
            ]);

            // Trigger Piper integration if share URL is present.
            // Use chatgpt_id as filename if available, otherwise conversation id.
            if (!empty($this->shareUrl)) {
                $piperInbox = '/home/craigpar/Code/Piper/share_inbox';
                if (!is_dir($piperInbox)) {
                    @mkdir($piperInbox, 0755, true);
                }
                
                $jobId = $chatgptId ?? $conversation->id;
                $filename = "{$jobId}.share.url";
                
                // Content of file is the share URL
                @file_put_contents("{$piperInbox}/{$filename}", $this->shareUrl);

                session()->flash('success', 'Conversation connected to Piper!');
            }
        }
    }

	public function render()
	{
		// Keep rendering logic minimal and predictable.
		return view('livewire.chat-panel');
	}
}
