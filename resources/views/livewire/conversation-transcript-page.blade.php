<div class="project-page-container">
    {{-- HEADER: Back Links --}}
    <div class="page-header grid-x grid-padding-x align-middle" style="padding:5px;">
        <div class="cell shrink">
            <button
                type="button"
                onclick="window.location='{{ route('projects.show', $project) }}'"
                class="{{ $pageHeaderClasses }}"
            >
                Back to Conversations
            </button>
        </div>
        <div class="cell auto"></div>
        <div class="cell shrink">
            <button
                type="button"
                onclick="window.location='{{ route('projects.index') }}'"
                class="{{ $pageHeaderClasses }}"
            >
                Back to Projects
            </button>
        </div>
    </div>

    {{-- BODY: Transcript --}}
    <div class="page-body grid-container">
        <div class="grid-x grid-margin-x">
            <div class="cell">
                <h1 class="h2 text-glow">
                    {{ $conversation->title ?? 'Untitled Conversation' }}
                </h1>
                <div class="subheader">
                    Share: <a href="{{ $conversation->share_url }}" target="_blank" rel="noopener noreferrer">{{ $conversation->share_url }}</a>
                </div>
                <div class="subheader">
                    Updated {{ $conversation->updated_at->diffForHumans() }}
                </div>
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

        <div class="grid-x grid-margin-x" style="margin-bottom: 2rem;">
            <div class="cell">
                <div class="callout" style="{{ $callOutStyle }}">
                    <h3 style="margin-bottom: 0.5rem;">Edit Current Transcript</h3>
                    <form wire:submit.prevent="saveTranscriptEdits">
                        <div class="grid-x grid-margin-x" style="margin-bottom: 1rem;">
                            <div class="cell text-center">
                                <label style="display: flex; flex-direction: column; align-items: center;">Transcript Content
                                    <textarea
                                        id="editable-transcript-textarea"
                                        style="margin: 0 auto; width: 90%;"
                                        wire:model.defer="editableTranscript"
                                        rows="12"
                                        placeholder="Edit the full conversation transcript here..."
                                    ></textarea>
                                </label>
                                @error('editableTranscript')
                                    <p class="form-error is-visible" style="margin-top: 0.5rem;">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="button-group align-right">
                            <button type="submit" class="button warning small">Save Transcript</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid-x grid-margin-x" style="margin-bottom: 2rem;">
            <div class="cell">
                <div class="callout" style="{{ $callOutStyle }}">
                    <h3 style="margin-bottom: 0.5rem;">Add to Conversation</h3>
                    <form wire:submit.prevent="addToConversation">
                        <div class="grid-x grid-margin-x" style="margin-bottom: 1rem;">
                            <div class="cell text-center">
                                <label style="display: flex; flex-direction: column; align-items: center;">New Entry
                                    <textarea
                                        id="new-entry-textarea"
                                        style="margin: 0 auto; width: 90%;"
                                        wire:model.defer="newEntry"
                                        rows="6"
                                        placeholder="Add notes, decisions, or follow-up context..."
                                    ></textarea>
                                </label>
                                @error('newEntry')
                                    <p class="form-error is-visible" style="margin-top: 0.5rem;">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="button-group align-right">
                            <button type="submit" class="button small">Append Entry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="conversation-transcript-panel">
            <div class="markdown-content conversation-transcript-body prose prose-slate max-w-none dark:prose-invert">
                {!! $conversationHtml !!}
            </div>
        </div>
    </div>

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
