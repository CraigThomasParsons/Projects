<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

/**
 * RegistryEditorPage — Bridgit registry viewer and editor.
 *
 * Reads the Bridgit TOML registry from disk, displays all repo entries
 * in an editable table, and writes changes back to the TOML file.
 * This gives operators a UI to correct IDs, aliases, paths, and links
 * without manually editing TOML.
 */
class RegistryEditorPage extends Component
{
    /** @var string Absolute path to the Bridgit registry file. */
    public string $registryPath = '';

    /** @var array<int, array> Flattened list of repo entries for the table. */
    public array $repos = [];

    /** @var int|null Index of the repo currently being edited, or null. */
    public ?int $editingIndex = null;

    /** @var string Search/filter term for the repo list. */
    public string $search = '';

    /** @var string Flash message shown after save/delete operations. */
    public string $flashMessage = '';

    /**
     * Livewire mount — runs once when the page loads.
     * Resolves the registry path and loads all entries.
     */
    public function mount(): void
    {
        $home = getenv('HOME') ?: '/tmp';
        $this->registryPath = $home . '/Code/bridgit-sync-engine/registry/repo_registry.toml';

        $this->loadRegistry();
    }

    /**
     * Load the TOML registry from disk and flatten into the $repos array.
     * Each entry becomes an associative array with the fields we care about.
     */
    public function loadRegistry(): void
    {
        if (!file_exists($this->registryPath)) {
            $this->repos = [];
            $this->flashMessage = 'Registry file not found at: ' . $this->registryPath;
            return;
        }

        $raw = file_get_contents($this->registryPath);
        $parsed = $this->parseToml($raw);
        $this->repos = $parsed;
        $this->flashMessage = '';
    }

    /**
     * Enter edit mode for a specific repo row.
     * Converts the aliases array to a comma-separated string for the input field.
     */
    public function editRepo(int $index): void
    {
        $this->editingIndex = $index;

        // Flatten aliases into a comma-separated string for editing.
        $this->repos[$index]['aliases_raw'] = implode(', ', $this->repos[$index]['aliases'] ?? []);
    }

    /**
     * Cancel editing and reload from disk.
     */
    public function cancelEdit(): void
    {
        $this->editingIndex = null;
        $this->loadRegistry();
    }

    /**
     * Save a single repo's changes and write the full registry back to TOML.
     * Converts the comma-separated aliases_raw back into the aliases array.
     */
    public function saveRepo(int $index): void
    {
        // Parse the comma-separated alias string back into an array.
        $rawAliases = $this->repos[$index]['aliases_raw'] ?? '';
        $this->repos[$index]['aliases'] = array_values(array_filter(
            array_map('trim', explode(',', $rawAliases)),
            fn(string $s) => $s !== ''
        ));
        unset($this->repos[$index]['aliases_raw']);

        $this->editingIndex = null;
        $this->writeRegistry();
        $this->flashMessage = 'Saved: ' . ($this->repos[$index]['id'] ?? 'unknown');
    }

    /**
     * Delete a repo entry from the registry after confirmation.
     */
    public function deleteRepo(int $index): void
    {
        $deletedId = $this->repos[$index]['id'] ?? 'unknown';
        array_splice($this->repos, $index, 1);
        $this->editingIndex = null;
        $this->writeRegistry();
        $this->flashMessage = 'Deleted: ' . $deletedId;
    }

    /**
     * Return the filtered list of repos based on the search term.
     *
     * @return array<int, array{index: int, repo: array}>
     */
    public function getFilteredReposProperty(): array
    {
        $filtered = [];
        $term = strtolower(trim($this->search));

        foreach ($this->repos as $i => $repo) {
            if ($term === '') {
                $filtered[] = ['index' => $i, 'repo' => $repo];
                continue;
            }

            // Search across ID, local path, GitHub name, aliases, and chat project name.
            $haystack = strtolower(implode(' ', [
                $repo['id'] ?? '',
                $repo['local_path'] ?? '',
                $repo['github_name'] ?? '',
                $repo['github_url'] ?? '',
                $repo['chat_project_name'] ?? '',
                implode(' ', $repo['aliases'] ?? []),
            ]));

            if (str_contains($haystack, $term)) {
                $filtered[] = ['index' => $i, 'repo' => $repo];
            }
        }

        return $filtered;
    }

    /**
     * Render the page with the app layout.
     */
    public function render()
    {
        return view('livewire.registry-editor-page', [
            'filteredRepos' => $this->filteredRepos,
        ])->layout('layouts.app');
    }

    /**
     * Parse the Bridgit TOML registry into a flat array of repo entries.
     *
     * This is a purpose-built parser for the specific [[repo]] TOML schema
     * used by Bridgit — not a general TOML parser. It handles the nested
     * [repo.chat], [repo.github], [repo.local], [repo.aliases] sections.
     *
     * @return array<int, array>
     */
    private function parseToml(string $raw): array
    {
        $repos = [];
        $lines = explode("\n", $raw);
        $current = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip empty lines and comments.
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            // New repo block.
            if ($trimmed === '[[repo]]') {
                if ($current !== null) {
                    $repos[] = $current;
                }
                $current = [
                    'id' => '',
                    'local_path' => '',
                    'github_name' => '',
                    'github_url' => '',
                    'chat_project_name' => '',
                    'chat_project_id' => '',
                    'aliases' => [],
                ];
                continue;
            }

            // Skip section headers — we handle values directly.
            if (preg_match('/^\[repo\.\w+\]$/', $trimmed)) {
                continue;
            }

            // Parse key = value pairs.
            if ($current !== null && str_contains($trimmed, '=')) {
                [$key, $value] = array_map('trim', explode('=', $trimmed, 2));

                // Strip TOML string quotes.
                $cleanValue = trim($value, "'\"");

                match ($key) {
                    'id' => $current['id'] = $cleanValue,
                    'path' => $current['local_path'] = $cleanValue,
                    'name' => $current['github_name'] = $cleanValue,
                    'url' => $current['github_url'] = $cleanValue,
                    'project_name' => $current['chat_project_name'] = $cleanValue,
                    'project_id' => $current['chat_project_id'] = $cleanValue,
                    'names' => $current['aliases'] = $this->parseTomlArray($value),
                    'paths' => null, // We read but don't expose alias paths in the UI.
                    default => null,
                };
            }
        }

        // Don't forget the last entry.
        if ($current !== null) {
            $repos[] = $current;
        }

        return $repos;
    }

    /**
     * Parse a TOML inline array like ['foo', 'bar'] into a PHP array.
     *
     * @return string[]
     */
    private function parseTomlArray(string $raw): array
    {
        $inner = trim($raw, '[] ');
        if ($inner === '') {
            return [];
        }

        $items = [];
        foreach (explode(',', $inner) as $item) {
            $clean = trim(trim($item), "'\"");
            if ($clean !== '') {
                $items[] = $clean;
            }
        }

        return $items;
    }

    /**
     * Serialize the $repos array back to Bridgit's TOML format and write to disk.
     */
    private function writeRegistry(): void
    {
        $lines = [];

        foreach ($this->repos as $repo) {
            $lines[] = '[[repo]]';
            $lines[] = "id = '" . ($repo['id'] ?? '') . "'";
            $lines[] = '';
            $lines[] = '[repo.chat]';
            $lines[] = "project_name = '" . ($repo['chat_project_name'] ?? '') . "'";
            $lines[] = "project_id = '" . ($repo['chat_project_id'] ?? '') . "'";
            $lines[] = '';
            $lines[] = '[repo.github]';
            $lines[] = "name = '" . ($repo['github_name'] ?? '') . "'";
            $lines[] = "url = '" . ($repo['github_url'] ?? '') . "'";
            $lines[] = '';
            $lines[] = '[repo.local]';
            $lines[] = "path = '" . ($repo['local_path'] ?? '') . "'";
            $lines[] = '';
            $lines[] = '[repo.aliases]';
            $lines[] = 'names = ' . $this->toTomlArray($repo['aliases'] ?? []);
            $lines[] = 'paths = []';
            $lines[] = '';
        }

        file_put_contents($this->registryPath, implode("\n", $lines));
    }

    /**
     * Convert a PHP string array into a TOML inline array string.
     *
     * @param string[] $items
     */
    private function toTomlArray(array $items): string
    {
        if (empty($items)) {
            return '[]';
        }

        $quoted = array_map(fn(string $s) => "'" . $s . "'", $items);
        return '[' . implode(', ', $quoted) . ']';
    }
}
