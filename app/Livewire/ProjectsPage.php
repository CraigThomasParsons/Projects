<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Project;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Manages the projects overview and the new project modal.
 *
 * This component is responsible for:
 * - Listing all projects
 * - Collecting basic project metadata
 * - Linking to the per-project conversations page
 */
class ProjectsPage extends Component
{
    public Collection $projects;

    public string $projectName = '';
    public string $projectDescription = '';
    public bool $showAddProjectModal = false;

    /**
     * Persist a new project with basic metadata.
     */
    public function saveProject(): void
    {
        logger()->info('saveProject called', [
            'name' => $this->projectName,
            'description' => $this->projectDescription,
        ]);
        // Validate inputs to keep project metadata clean.
        $validatedData = $this->validate([
            'projectName' => ['required', 'string', 'max:255'],
            'projectDescription' => ['nullable', 'string'],
        ]);

        // Persist the project record so it appears in the list and links work.
        Project::create([
            'name' => $validatedData['projectName'],
            'description' => $validatedData['projectDescription'],
        ]);

        // Refresh the project list and reset the form for the next entry.
        $this->loadProjects();
        $this->resetProjectForm();

        $this->showAddProjectModal = false;

        session()->flash('success', 'Project created.');
    }

    /**
     * Load projects when the component mounts.
     */
    public function mount(): void
    {
        // Fetch projects so the list renders on first paint.
        $this->loadProjects();
    }

    /**
     * Refresh the project collection.
     */
    private function loadProjects(): void
    {
        // Order by latest so new projects bubble to the top.
        $this->projects = Project::latest()->get();
    }

    /**
     * Reveal the add project modal and reset form inputs.
     */
    public function openAddProjectForm(): void
    {
        // Clear out the previous project data.
        $this->projectName = '';
        $this->projectDescription = '';
        $this->showAddProjectModal = true;
    }

    public function resetProjectForm(): void
    {
        // Clear out the previous project data.
        $this->projectName = '';
        $this->projectDescription = '';
        $this->showAddProjectModal = false;
    }

    /**
     * Render the component view.
     */
    public function render()
    {
        return view('livewire.projects-page')
            ->layout('layouts.app');
    }
}
