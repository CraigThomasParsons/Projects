<div class="grid-container">
    <div class="grid-x grid-margin-x align-middle">
        <div class="cell small-6">
            <h1 class="h2">Projects</h1>
        </div>
        <div class="cell small-6 text-right">
                @if ($showAddProjectModal === false)
                    <button
                        class="button primary"
                        wire:click="$set('showAddProjectModal', true)"
                    >
                        + New Project
                    </button>
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

    @if ($showAddProjectModal === false)
    <div class="grid-x grid-margin-x">
        <div class="cell">
            <h2 class="h4">Project List</h2>
            <ul class="no-bullet">
                @forelse ($projects as $project)
                    <li class="project-list-item">
                        <a
                            href="{{ route('projects.show', $project) }}"
                            class="project-link"
                        >
                            <strong>{{ $project->name }}</strong>
                            @if ($project->description)
                                <div class="subheader">
                                    {{ \Illuminate\Support\Str::limit($project->description, 160) }}
                                </div>
                            @endif
                        </a>
                    </li>
                @empty
                    <li class="text-muted">No projects found</li>
                @endforelse
            </ul>
        </div>
    </div>
    @endif    

    {{-- Foundation Reveal Modal, no actual reveal is necessary we are just toggling visibility --}}
    @if ($showAddProjectModal)
    <div
        class="modal modal-full"
        wire:keydown.escape="$set('showAddProjectModal', false)"
        tabindex="0"
    >
        <h2 id="project-modal-title">Add Project</h2>

        <button
            class="close-button"
            aria-label="Close modal"
            type="button"
            wire:click="$set('showAddProjectModal', false)"
        >
            <span aria-hidden="true">&times;</span>
        </button>

        <form>
            <label>Name
                <input
                    type="text"
                    wire:model="projectName"
                    placeholder="Project name"
                />
            </label>
            @error('projectName')
                <p class="form-error is-visible">{{ $message }}</p>
            @enderror

            <label>Description
                <textarea
                    wire:model="projectDescription"
                    placeholder="Short description"
                    rows="4"
                ></textarea>
            </label>
            @error('projectDescription')
                <p class="form-error is-visible">{{ $message }}</p>
            @enderror

            <div class="button-group align-right">
                <button
                    type="button"
                    class="button secondary hollow"
                    wire:click="$set('showAddProjectModal', false)"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    class="button primary"
                    wire:click="saveProject"
                    wire:loading.attr="disabled"
                    wire:target="saveProject"
                >
                    Save
                </button>
            </div>
        </form>
    </div>
    @endif
</div>
