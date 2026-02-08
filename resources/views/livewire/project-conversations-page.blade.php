<div class="grid-container">
    <div class="grid-x grid-margin-x align-middle">
        <div class="cell auto">
            <h1 class="h2">{{ $project->name }}</h1>
            @if ($project->description)
                <p class="subheader">{{ $project->description }}</p>
            @endif
        </div>
        <div class="cell shrink text-right">
            <a
                href="{{ route('projects.index') }}"
                class="button hollow secondary small"
            >
                &#x2190; Back to Projects
            </a>
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

    <div class="grid-x grid-margin-x align-middle">
        <div class="cell auto">
            <h2 class="h4">Conversations</h2>
        </div>
        <div class="cell shrink text-right">
            <button
                wire:click="openConversationModal"
                class="button primary"
            >
                + New Conversation
            </button>
        </div>
    </div>

    <ul class="no-bullet">
        @forelse ($conversations as $conversation)
            <li class="callout secondary">
                <div class="grid-x grid-margin-x align-middle">
                    <div class="cell auto">
                        <strong>{{ $conversation->title ?? 'Untitled Conversation' }}</strong>
                        <div class="subheader">
                            Share: <a href="{{ $conversation->share_url }}" target="_blank" rel="noopener noreferrer">{{ $conversation->share_url }}</a>
                        </div>
                        <div class="subheader">Updated {{ $conversation->updated_at->diffForHumans() }}</div>
                    </div>
                    <div class="cell shrink">
                        <button
                            wire:click="syncConversation({{ $conversation->id }})"
                            class="button secondary"
                        >
                            Sync
                        </button>
                    </div>
                </div>
            </li>
        @empty
            <li class="text-muted">No conversations yet. Add one to start.</li>
        @endforelse
    </ul>

    @if ($showConversationModal)
        <div class="reveal-overlay" style="display: block;">
            <div
                class="reveal small"
                style="display: block;"
                role="dialog"
                aria-modal="true"
                wire:keydown.escape="closeConversationModal"
            >
                <h2>Add Conversation</h2>

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
                        wire:click="closeConversationModal"
                        class="button secondary hollow"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="saveConversation"
                        class="button primary"
                    >
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
