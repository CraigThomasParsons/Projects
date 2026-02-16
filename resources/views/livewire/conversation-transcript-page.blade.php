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

        <div class="conversation-transcript-panel">
            <div class="markdown-content conversation-transcript-body">
                {!! $conversationHtml !!}
            </div>
        </div>
    </div>
</div>
