<div class="project-page-container">
    {{-- HEADER: Back Links --}}
    <div class="page-header grid-x grid-padding-x align-middle" style="padding:5px;">
        <div class="cell shrink">
            <a
                href="{{ route('projects.show', $project) }}"
                class="button hollow secondary small"
            >
                &#x2190; Back to Conversations
            </a>
        </div>
        <div class="cell auto"></div>
        <div class="cell shrink">
            <a
                href="{{ route('projects.index') }}"
                class="button hollow secondary small"
            >
                &#x2190; Back to Projects
            </a>
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

        <div class="callout" style="margin-top: 0.75rem;">
            <h3 style="margin-bottom: 0.5rem;">Edit Current Transcript</h3>
            <form wire:submit.prevent="saveTranscriptEdits">
                <label for="editable-transcript-textarea" class="show-for-sr">Editable transcript</label>
                <textarea
                    id="editable-transcript-textarea"
                    wire:model.defer="editableTranscript"
                    rows="12"
                    placeholder="Edit the full conversation transcript here..."
                ></textarea>

                @error('editableTranscript')
                    <p class="form-error is-visible" style="margin-top: 0.5rem;">{{ $message }}</p>
                @enderror

                <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button type="submit" class="button warning small">Save Transcript</button>
                </div>
            </form>
        </div>

        <div class="callout" style="margin-top: 0.75rem;">
            <h3 style="margin-bottom: 0.5rem;">Add to Conversation</h3>
            <form wire:submit.prevent="addToConversation">
                <label for="new-entry-textarea" class="show-for-sr">New conversation entry</label>
                <textarea
                    id="new-entry-textarea"
                    wire:model.defer="newEntry"
                    rows="6"
                    placeholder="Add notes, decisions, or follow-up context..."
                ></textarea>

                @error('newEntry')
                    <p class="form-error is-visible" style="margin-top: 0.5rem;">{{ $message }}</p>
                @enderror

                <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button type="submit" class="button small">Append Entry</button>
                </div>
            </form>
        </div>

        <div class="conversation-transcript-panel">
            <div class="markdown-content conversation-transcript-body prose prose-slate max-w-none dark:prose-invert">
                {!! $conversationHtml !!}
            </div>
        </div>
    </div>
</div>
