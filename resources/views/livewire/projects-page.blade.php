<div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">
        ChatProjects
    </h1>

    <button
        wire:click="openModal"
        class="px-3 py-1 border rounded mb-4"
    >
        + New Conversation
    </button>

    <ul class="list-disc ml-6 space-y-1">
        @forelse ($projects as $project)
            <li>{{ $project->name }}</li>
        @empty
            <li class="text-gray-500">No projects found</li>
        @endforelse
    </ul>

    @if ($showModal)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center"
            wire:keydown.escape="closeModal"
            tabindex="0"
        >
            <div class="bg-white text-black p-6 rounded shadow w-96">
                <h2 class="text-lg font-bold mb-4">
                    Add Conversation
                </h2>

                <input
                    type="text"
                    wire:model="shareUrl"
                    placeholder="Paste ChatGPT share URL"
                    class="w-full border p-2 mb-4"
                />

                <div class="flex justify-end gap-2">
                    <button
                        wire:click="closeModal"
                        class="px-3 py-1 border rounded"
                    >
                        Cancel
                    </button>

                    <button
                        class="px-3 py-1 border rounded bg-gray-200"
                    >
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
