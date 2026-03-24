<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\ProjectAlias;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class ProjectAliasesManager extends Component
{
    public string $newAliasName = '';
    public ?int $selectedProjectId = null;

    /**
     * Render the component view.
     */
    public function render()
    {
        // Load all active projects, ordered by name, with their aliases eager-loaded
        $projects = Project::orderBy('name')->with('aliases')->get();

        return view('livewire.project-aliases-manager', [
            'projects' => $projects,
        ]);
    }

    /**
     * Add a new alias to the selected project.
     */
    public function addAlias(): void
    {
        // Require both a selected project and a non-empty name
        if ($this->selectedProjectId === null || trim($this->newAliasName) === '') {
            return;
        }

        // Validate the alias is globally unique
        $normalizedAlias = trim($this->newAliasName);
        $aliasExists = ProjectAlias::where('alias', 'ilike', $normalizedAlias)->exists();

        if ($aliasExists) {
            $this->addError('newAliasName', 'This alias already exists on a project.');
            return;
        }

        // Attach the alias to the project
        ProjectAlias::create([
            'project_id' => $this->selectedProjectId,
            'alias' => $normalizedAlias,
        ]);

        // Reset the input field
        $this->newAliasName = '';
    }

    /**
     * Delete an existing alias by its ID.
     */
    public function removeAlias(int $aliasId): void
    {
        $aliasRecord = ProjectAlias::find($aliasId);

        if ($aliasRecord !== null) {
            $aliasRecord->delete();
        }
    }
}
