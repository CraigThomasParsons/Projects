<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;
use App\Models\Conversation;
use Illuminate\Support\Collection;


class ProjectsPage extends Component
{
    public bool $showModal = false;
    public string $shareUrl = '';

    /** @var Collection */
    public $projects;

    /** @var Collection */
    public $conversations;

    /**
     * Opens the modal, by setting the showModal property to true.
     */
    public function openModal(): void
    {
        $this->showModal = true;
    }

    /**
     * Closes the modal, by setting the showModal property to false
     */
    public function closeModal(): void
    {
        $this->showModal = false;
    }

    /**
     * Saves the conversation by creating a new Conversation record.
     */
    public function save(): void
    {
        $project = Project::first(); // temporary

        Conversation::create([
            'project_id' => $project->id,
            'share_url' => $this->shareUrl,
        ]);

        $this->closeModal();
    }

    /**
     * Mounts the component, loading projects and conversations.
     */
    public function mount(): void
    {
        $this->projects = Project::all();
        $this->conversations = Conversation::latest()->get();
    }

    /**
     * Renders the component, see app.blade.php
    */
    public function render()
    {
        return view('livewire.projects-page')
            ->layout('layouts.app');
    }
}
