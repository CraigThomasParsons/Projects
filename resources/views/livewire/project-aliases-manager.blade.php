<div class="tc-section" style="margin-top: 2rem;">
    <h3>Project Ingestion Aliases</h3>
    <p style="margin-bottom: 1rem; color: var(--tc-text-muted, #aaa); font-size: 0.9em;">
        Map external folder names (e.g., from the Pipeline Extractor) to your internal ChatProjects database.
    </p>

    <!-- Add Alias Form -->
    <form wire:submit="addAlias" style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <select wire:model="selectedProjectId" style="flex: 1; min-width: 200px; padding: 0.5rem; border-radius: 4px; border: 1px solid var(--tc-border, #444); background: var(--tc-card-bg, #222); color: var(--tc-text, #fff);">
            <option value="">-- Select Project --</option>
            @foreach($projects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
            @endforeach
        </select>

        <input type="text" wire:model="newAliasName" placeholder="e.g. ContextControlledDevelopmentFactory" style="flex: 2; min-width: 250px; padding: 0.5rem; border-radius: 4px; border: 1px solid var(--tc-border, #444); background: var(--tc-card-bg, #222); color: var(--tc-text, #fff);">

        <button type="submit" class="tc-btn" style="white-space: nowrap;">
            ➕ Add Alias
        </button>
    </form>

    @error('newAliasName')
        <div style="color: #ff6b6b; margin-top: -1rem; margin-bottom: 1rem; font-size: 0.85em;">{{ $message }}</div>
    @enderror

    <!-- Aliases Table / List -->
    <div style="background: var(--tc-card-bg, #2a2a2a); border: 1px solid var(--tc-border, #333); border-radius: 6px; overflow: hidden;">
        <table style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="background: rgba(0,0,0,0.2);">
                    <th style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">Known Alias</th>
                    <th style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">Mapped Project</th>
                    <th style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333); width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @php $hasAliases = false; @endphp
                @foreach($projects as $project)
                    @foreach($project->aliases as $alias)
                        @php $hasAliases = true; @endphp
                        <tr>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333); font-family: monospace; font-size: 0.9em;">
                                {{ $alias->alias }}
                            </td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333); color: var(--tc-text-muted, #ccc);">
                                {{ $project->name }}
                            </td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">
                                <button wire:click="removeAlias({{ $alias->id }})" wire:confirm="Are you sure you want to remove this alias?" style="background: none; border: none; color: #ff6b6b; cursor: pointer; padding: 0.25rem 0.5rem; border-radius: 4px;" onmouseover="this.style.background='rgba(255,107,107,0.1)'" onmouseout="this.style.background='none'">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endforeach

                @if(!$hasAliases)
                    <tr>
                        <td colspan="3" style="padding: 1.5rem; text-align: center; color: var(--tc-text-muted, #777); font-style: italic;">
                            No aliases configured yet.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
