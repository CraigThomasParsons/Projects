<div class="tc-page">
    {{-- Navigation --}}
    <a href="{{ route('projects.index') }}" class="tc-back">&larr; Back to Projects</a>

    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <h1 class="h2" style="margin: 0;">Bridgit Registry</h1>
        <span style="color: var(--tc-text-muted, #888); font-size: 0.9em;">
            {{ count($repos) }} repo(s) &middot; {{ count($filteredRepos) }} shown
        </span>
    </div>

    {{-- Flash Message --}}
    @if($flashMessage)
        <div style="background: rgba(0,255,136,0.1); border: 1px solid rgba(0,255,136,0.3); color: #00ff88; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.9em;">
            {{ $flashMessage }}
        </div>
    @endif

    {{-- Search Bar --}}
    <div style="margin-bottom: 1.5rem;">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search by ID, path, GitHub name, alias..."
            style="width: 100%; padding: 0.6rem 1rem; border-radius: 6px; border: 1px solid var(--tc-border, #444); background: var(--tc-card-bg, #222); color: var(--tc-text, #fff); font-size: 0.95em;"
        >
    </div>

    {{-- Registry Table --}}
    <div style="background: var(--tc-card-bg, #2a2a2a); border: 1px solid var(--tc-border, #333); border-radius: 6px; overflow-x: auto;">
        <table style="width: 100%; text-align: left; border-collapse: collapse; min-width: 900px;">
            <thead>
                <tr style="background: rgba(0,0,0,0.2);">
                    <th style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333); width: 40px;">#</th>
                    <th style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">ID</th>
                    <th style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">Local Path</th>
                    <th style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">GitHub</th>
                    <th style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">Chat Project</th>
                    <th style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">Aliases</th>
                    <th style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333); width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filteredRepos as $entry)
                    @php $i = $entry['index']; $repo = $entry['repo']; @endphp

                    @if($editingIndex === $i)
                        {{-- ═══ EDIT MODE ═══ --}}
                        <tr style="background: rgba(0,200,255,0.05);">
                            <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--tc-border, #333); color: var(--tc-text-muted, #888); font-size: 0.85em;">
                                {{ $i + 1 }}
                            </td>
                            <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">
                                <input type="text" wire:model="repos.{{ $i }}.id"
                                    style="width: 100%; padding: 0.3rem 0.5rem; border-radius: 4px; border: 1px solid var(--tc-border, #555); background: var(--tc-card-bg, #1a1a1a); color: var(--tc-text, #fff); font-family: monospace; font-size: 0.85em;">
                            </td>
                            <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">
                                <input type="text" wire:model="repos.{{ $i }}.local_path"
                                    style="width: 100%; padding: 0.3rem 0.5rem; border-radius: 4px; border: 1px solid var(--tc-border, #555); background: var(--tc-card-bg, #1a1a1a); color: var(--tc-text, #fff); font-family: monospace; font-size: 0.85em;">
                            </td>
                            <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">
                                <input type="text" wire:model="repos.{{ $i }}.github_name" placeholder="GitHub name"
                                    style="width: 100%; padding: 0.3rem 0.5rem; border-radius: 4px; border: 1px solid var(--tc-border, #555); background: var(--tc-card-bg, #1a1a1a); color: var(--tc-text, #fff); font-size: 0.85em; margin-bottom: 0.25rem;">
                                <input type="text" wire:model="repos.{{ $i }}.github_url" placeholder="Clone URL"
                                    style="width: 100%; padding: 0.3rem 0.5rem; border-radius: 4px; border: 1px solid var(--tc-border, #555); background: var(--tc-card-bg, #1a1a1a); color: var(--tc-text, #fff); font-size: 0.85em;">
                            </td>
                            <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">
                                <input type="text" wire:model="repos.{{ $i }}.chat_project_name" placeholder="Project name"
                                    style="width: 100%; padding: 0.3rem 0.5rem; border-radius: 4px; border: 1px solid var(--tc-border, #555); background: var(--tc-card-bg, #1a1a1a); color: var(--tc-text, #fff); font-size: 0.85em; margin-bottom: 0.25rem;">
                                <input type="text" wire:model="repos.{{ $i }}.chat_project_id" placeholder="Project ID"
                                    style="width: 100%; padding: 0.3rem 0.5rem; border-radius: 4px; border: 1px solid var(--tc-border, #555); background: var(--tc-card-bg, #1a1a1a); color: var(--tc-text, #fff); font-size: 0.85em;">
                            </td>
                            <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">
                                <input type="text" wire:model="repos.{{ $i }}.aliases_raw"
                                    placeholder="comma-separated"
                                    wire:keydown.enter="saveRepo({{ $i }})"
                                    style="width: 100%; padding: 0.3rem 0.5rem; border-radius: 4px; border: 1px solid var(--tc-border, #555); background: var(--tc-card-bg, #1a1a1a); color: var(--tc-text, #fff); font-size: 0.85em;">
                            </td>
                            <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">
                                <div style="display: flex; gap: 0.25rem;">
                                    <button wire:click="saveRepo({{ $i }})" class="tc-btn" style="font-size: 0.8em; padding: 0.3rem 0.6rem;">
                                        Save
                                    </button>
                                    <button wire:click="cancelEdit" style="font-size: 0.8em; padding: 0.3rem 0.6rem; background: none; border: 1px solid var(--tc-border, #555); color: var(--tc-text-muted, #aaa); border-radius: 4px; cursor: pointer;">
                                        Cancel
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @else
                        {{-- ═══ VIEW MODE ═══ --}}
                        <tr style="transition: background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333); color: var(--tc-text-muted, #888); font-size: 0.85em;">
                                {{ $i + 1 }}
                            </td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333); font-family: monospace; font-size: 0.85em; font-weight: 600;">
                                {{ $repo['id'] }}
                            </td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333); font-family: monospace; font-size: 0.8em; color: var(--tc-text-muted, #ccc); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $repo['local_path'] ?: '—' }}
                            </td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333); font-size: 0.85em;">
                                @if($repo['github_name'])
                                    <span style="color: var(--tc-text, #fff);">{{ $repo['github_name'] }}</span>
                                @else
                                    <span style="color: var(--tc-text-muted, #666);">—</span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333); font-size: 0.85em;">
                                @if($repo['chat_project_name'])
                                    <span style="color: var(--tc-text, #fff);">{{ $repo['chat_project_name'] }}</span>
                                @else
                                    <span style="color: var(--tc-text-muted, #666);">—</span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333); font-size: 0.8em; color: var(--tc-text-muted, #aaa); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                @if(!empty($repo['aliases']))
                                    {{ implode(', ', $repo['aliases']) }}
                                @else
                                    <span style="color: var(--tc-text-muted, #666);">—</span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--tc-border, #333);">
                                <div style="display: flex; gap: 0.25rem;">
                                    <button wire:click="editRepo({{ $i }})" style="background: none; border: none; color: var(--tc-text-muted, #aaa); cursor: pointer; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85em;" onmouseover="this.style.color='var(--tc-text, #fff)'" onmouseout="this.style.color='var(--tc-text-muted, #aaa)'">
                                        Edit
                                    </button>
                                    <button wire:click="deleteRepo({{ $i }})" wire:confirm="Delete '{{ $repo['id'] }}' from the registry?" style="background: none; border: none; color: #ff6b6b; cursor: pointer; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85em; opacity: 0.6;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="7" style="padding: 2rem; text-align: center; color: var(--tc-text-muted, #777); font-style: italic;">
                            @if($search)
                                No repos match "{{ $search }}"
                            @else
                                Registry is empty. Run <code>make run</code> in bridgit-sync-engine first.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer Info --}}
    <div style="margin-top: 1rem; font-size: 0.8em; color: var(--tc-text-muted, #666);">
        Registry file: <code style="font-size: 0.9em;">{{ $registryPath }}</code>
    </div>
</div>
