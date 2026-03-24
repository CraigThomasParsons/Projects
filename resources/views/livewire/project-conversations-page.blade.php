<div class="project-page-container">
    <style>.edit-project-callout label { color: #d0d0d0 !important; }</style>
    {{-- HEADER: Back & Edit --}}
    <div class="page-header flex justify-between items-center p-2 mb-4" style="{{ $pageHeaderOverrideStyle }}">
        <div>
            <button
                onclick="window.location='{{ route('projects.index') }}'"
                class=" {{ $pageHeaderClasses }}"
            >
                Back to Projects
            </button>
        </div>
        <div>
            <a
                href="{{ route('projects.inception.wizard', $project) }}"
                class="button success"
            >
                Start Lean Inception
            </a>
            <button
                wire:click="openEditProjectForm"
                class=" {{ $pageHeaderClasses }} "
            >
                Edit Project
            </button>
        </div>
    </div>

    {{-- BODY: Content --}}
    <div class="page-body grid-container">
        @if ($showEditProjectForm)
            <div class="grid-x grid-margin-x">
                <div class="cell">
                    <div class="callout edit-project-callout" style="{{$wrongWayTodoThings}}">
                        <h2 class="h3">Edit Project</h2>
                        <div class="grid-x grid-margin-x" style="margin-bottom: 2rem;">
                            <div class="cell medium-8">
                                <label>Name
                                    <input
                                        type="text"
                                        wire:model="editProjectName"
                                        placeholder="Project name"
                                    />
                                </label>
                                @error('editProjectName')
                                    <p class="form-error is-visible">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="cell medium-4">
                                <label>Type
                                    <select wire:model="editProjectType">
                                        <option value="code">Code</option>
                                        <option value="idea">Idea</option>
                                    </select>
                                </label>
                                @error('editProjectType')
                                    <p class="form-error is-visible">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid-x grid-margin-x" style="margin-bottom: 2rem;">
                            <div class="cell text-center">
                                <label style="display: flex; flex-direction: column; align-items: center;">Description
                                    <textarea
                                        style="margin: 0 auto; width: 90%;"
                                        wire:model="editProjectDescription"
                                        placeholder="Short description"
                                        rows="6"
                                    ></textarea>
                                </label>
                                @error('editProjectDescription')
                                    <p class="form-error is-visible">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid-x grid-margin-x" style="margin-bottom: 2rem;">
                            <div class="cell medium-6">
                                <label>Local Location
                                    <input
                                        type="text"
                                        wire:model="editLocalLocation"
                                        placeholder="/home/user/Code/YourProject"
                                    />
                                </label>
                                @error('editLocalLocation')
                                    <p class="form-error is-visible">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="cell medium-6">
                                <label>GitHub Repo
                                    <input
                                        type="text"
                                        wire:model="editGithubRepo"
                                        placeholder="https://github.com/org/repo"
                                    />
                                </label>
                                @error('editGithubRepo')
                                    <p class="form-error is-visible">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid-x grid-margin-x" style="margin-bottom: 2rem;">
                            <div class="cell medium-6">
                                <label>Gitea Location
                                    <input
                                        type="text"
                                        wire:model="editGiteaLocation"
                                        placeholder="https://gitea.example.com/org/repo"
                                    />
                                </label>
                                @error('editGiteaLocation')
                                    <p class="form-error is-visible">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="cell medium-6">
                                <label>Languages
                                    <input
                                        type="text"
                                        wire:model="editLanguages"
                                        placeholder="PHP, JavaScript, Python"
                                    />
                                </label>
                                @error('editLanguages')
                                    <p class="form-error is-visible">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid-x grid-margin-x" style="margin-bottom: 2rem;">
                            <div class="cell text-center">
                                <label style="display: flex; flex-direction: column; align-items: center;">Framework Description
                                    <textarea
                                        style="margin: 0 auto; width: 90%;"
                                        wire:model="editFrameworkDescription"
                                        placeholder="Laravel 11 monolith with Livewire and PostgreSQL"
                                        rows="4"
                                    ></textarea>
                                </label>
                                @error('editFrameworkDescription')
                                    <p class="form-error is-visible">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="button-group align-right">
                            <button
                                type="button"
                                class="button secondary hollow"
                                wire:click="closeEditProjectForm"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="button primary"
                                wire:click="updateProject"
                                wire:loading.attr="disabled"
                                wire:target="updateProject"
                            >
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Project Title & Description --}}
            <div class="grid-x grid-margin-x">
                <div class="cell">
                    <h1 class="h2 text-glow" style="display: flex; align-items: center;">
                        {{ $project->name }}
                        @if ($project->type === 'idea')
                            <span class="label secondary" style="font-size: 0.5em; margin-left: 15px; border-radius: 4px;">Idea</span>
                        @else
                            <span class="label primary" style="font-size: 0.5em; margin-left: 15px; border-radius: 4px;">Code</span>
                        @endif
                    </h1>
                    @if ($project->description)
                        <div class="project-description markdown-content prose prose-slate max-w-none dark:prose-invert">
                            {!! Str::markdown($project->description) !!}
                        </div>
                    @endif

                    @if ($project->local_location || $project->github_repo || $project->gitea_location || $project->framework_description || $project->languages)
                        <div class="callout secondary" style="{{ $callOutSecondaryStyle }} ">
                            <h3 class="h6" style="margin-bottom: 0.5rem;">Code Context</h3>
                            @if ($project->local_location)
                                <div><strong>Local Location:</strong> {{ $project->local_location }}</div>
                            @endif
                            @if ($project->github_repo)
                                <div><strong>GithubRepo:</strong> {{ $project->github_repo }}</div>
                            @endif
                            @if ($project->gitea_location)
                                <div><strong>Git-teaLocation:</strong> {{ $project->gitea_location }}</div>
                            @endif
                            @if ($project->framework_description)
                                <div style="margin-top: 0.4rem;"><strong>Framework description:</strong> {{ $project->framework_description }}</div>
                            @endif
                            @if ($project->languages)
                                <div><strong>Languages:</strong> {{ $project->languages }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if (session()->has('success'))
                <div class="callout success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="callout alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Conversations Section --}}
            <div class="grid-x grid-margin-x align-middle">
                <div class="cell auto">
                    <h2 class="h4">Conversations</h2>
                </div>
                <div class="cell shrink text-right">
                    <button
                        wire:click="openShareUrlModal"
                        class="button secondary"
                    >
                        + Add Share URL
                    </button>
                    <button
                        wire:click="openChatLinkModal"
                        class="button secondary"
                    >
                        + Add Chat Link
                    </button>
                    <button
                        wire:click="openManualPasteModal"
                        class="button primary"
                    >
                        + Paste Transcript
                    </button>
                </div>
            </div>

            <ul class="no-bullet">
                @forelse ($conversations as $conversation)
                    <li class="callout secondary" style="{{ $callOutSecondaryStyle }} ">
                        <div class="grid-x grid-margin-x align-middle">
                            <div class="cell auto">
                                <strong>{{ $conversation->title ?? 'Untitled Conversation' }}</strong>
                                @if ($conversation->source_type === 'manual_paste')
                                    <div class="subheader">Source: Manual paste</div>
                                @elseif ($conversation->source_type === 'chat_link')
                                    <div class="subheader">Source: Chat link (Piper browser extraction)</div>
                                    <div class="subheader">
                                        Link: <a href="{{ $conversation->share_url }}" target="_blank" rel="noopener noreferrer">{{ $conversation->share_url }}</a>
                                    </div>
                                @elseif ($conversation->share_url)
                                    <div class="subheader">
                                        Share: <a href="{{ $conversation->share_url }}" target="_blank" rel="noopener noreferrer">{{ $conversation->share_url }}</a>
                                    </div>
                                @endif
                                <div class="subheader">Updated {{ $conversation->updated_at->diffForHumans() }}</div>
                                @if (!$conversation->raw_content)
                                    <div class="subheader">Not synced yet</div>
                                @endif
                            </div>
                            <div class="cell shrink">
                                @if ($conversation->raw_content)
                                    <a
                                        href="{{ route('conversations.show', [$project, $conversation]) }}"
                                        class="button secondary hollow"
                                    >
                                        View
                                    </a>
                                @endif
                                @if ($conversation->source_type !== 'manual_paste')
                                    <button
                                        wire:click="syncConversation({{ $conversation->id }})"
                                        class="button secondary"
                                    >
                                        Sync
                                    </button>
                                    <button
                                        wire:click="openMoveModal({{ $conversation->id }})"
                                        class="button secondary hollow"
                                    >
                                        Move
                                    </button>
                                @endif
                                <button
                                    wire:click="deleteConversation({{ $conversation->id }})"
                                    class="button alert hollow"
                                    onclick="return confirm('Delete this conversation?')"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="text-muted">No conversations yet. Add one to start.</li>
                @endforelse
            </ul>

            @if ($showShareUrlModal)
                <div class="reveal-overlay" style="display: block;">
                    <div
                        class="reveal small"
                        style="display: block;"
                        role="dialog"
                        aria-modal="true"
                        wire:keydown.escape="closeConversationModals"
                    >
                        <h2>Add Conversation by Share URL</h2>

                        <label>ChatGPT Share URL
                            <input
                                type="text"
                                wire:model.defer="shareUrl"
                                placeholder="https://chatgpt.com/share/..."
                            />
                        </label>

                        @error('shareUrl')
                            <p class="form-error is-visible">{{ $message }}</p>
                        @enderror

                        <div class="button-group align-right">
                            <button
                                wire:click="closeConversationModals"
                                class="button secondary hollow"
                            >
                                Cancel
                            </button>
                            <button
                                wire:click="saveConversation"
                                class="button primary"
                            >
                                Save Share URL
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if ($showChatLinkModal)
                <div class="reveal-overlay" style="display: block;">
                    <div
                        class="reveal small"
                        style="display: block;"
                        role="dialog"
                        aria-modal="true"
                        wire:keydown.escape="closeConversationModals"
                    >
                        <h2>Add Conversation by Chat Link</h2>

                        <label>ChatGPT Conversation Link (non-share)
                            <input
                                type="text"
                                wire:model.defer="chatConversationUrl"
                                placeholder="https://chatgpt.com/g/g-.../c/..."
                            />
                        </label>

                        <p class="help-text" style="margin-top: -0.25rem;">
                            This mode queues Piper browser extraction because private conversation pages are not publicly fetchable.
                        </p>

                        @error('chatConversationUrl')
                            <p class="form-error is-visible">{{ $message }}</p>
                        @enderror

                        <div class="button-group align-right">
                            <button
                                wire:click="closeConversationModals"
                                class="button secondary hollow"
                            >
                                Cancel
                            </button>
                            <button
                                wire:click="saveChatConversationLink"
                                class="button primary"
                            >
                                Save Chat Link
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if ($showManualPasteModal)
                <div class="reveal-overlay" style="display: block;">
                    <div
                        class="reveal small"
                        style="display: block;"
                        role="dialog"
                        aria-modal="true"
                        wire:keydown.escape="closeConversationModals"
                    >
                        <h2>Add Conversation by Manual Paste</h2>

                        <label>Title (optional)
                            <input
                                type="text"
                                wire:model.defer="manualConversationTitle"
                                placeholder="Conversation title"
                            />
                        </label>

                        <label>Transcript Markdown / Text
                            <textarea
                                wire:model.defer="manualConversationContent"
                                rows="8"
                                placeholder="Paste conversation content here..."
                            ></textarea>
                        </label>

                        @error('manualConversationTitle')
                            <p class="form-error is-visible">{{ $message }}</p>
                        @enderror

                        @error('manualConversationContent')
                            <p class="form-error is-visible">{{ $message }}</p>
                        @enderror

                        <div class="button-group align-right">
                            <button
                                wire:click="closeConversationModals"
                                class="button secondary hollow"
                            >
                                Cancel
                            </button>
                            <button
                                wire:click="savePastedConversation"
                                class="button primary"
                            >
                                Save Pasted
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if ($showMoveModal)
                <div class="reveal-overlay" style="display: block;">
                    <div
                        class="reveal small"
                        style="display: block;"
                        role="dialog"
                        aria-modal="true"
                        wire:keydown.escape="closeMoveModal"
                    >
                        <h2>Move Conversation</h2>

                        <label>Target Project
                            <select wire:model.defer="targetProjectId">
                                @foreach($availableProjects as $proj)
                                    <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        @error('targetProjectId')
                            <p class="form-error is-visible">{{ $message }}</p>
                        @enderror
                        @error('conversationToMoveId')
                            <p class="form-error is-visible">{{ $message }}</p>
                        @enderror

                        <div class="button-group align-right">
                            <button
                                wire:click="closeMoveModal"
                                class="button secondary hollow"
                            >
                                Cancel
                            </button>
                            <button
                                wire:click="moveConversation"
                                class="button primary"
                            >
                                Move Conversation
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- FOOTER: Delete Project --}}
    @if (!$showEditProjectForm)
    <div class="page-footer grid-x grid-padding-x align-middle">
        <div class="cell auto">
            <span class="danger-zone-label"></span>
        </div>
        <div class="cell shrink">
            <button
                class="button alert hollow"
                wire:click="deleteProject"
                onclick="return confirm('Delete this project and all conversations?')"
            >
                Delete Project
            </button>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            const syncTheme = () => {
                const theme = localStorage.getItem('theme') || 'cyberpunk';
                @this.call('setTheme', theme);
            };
            syncTheme();
            window.addEventListener('theme-changed', syncTheme);
        });
    </script>
</div>
