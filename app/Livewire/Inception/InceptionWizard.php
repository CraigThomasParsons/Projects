<?php

namespace App\Livewire\Inception;

use App\Models\Project;
use App\Models\Inception;
use Livewire\Component;

class InceptionWizard extends Component
{
    public Project $project;
    public Inception $inception;

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->inception = $project->inceptions()->firstOrCreate([], ['status' => 'vision']);
    }

    public function render()
    {
        return view('livewire.inception.inception-wizard')->layout('layouts.app');
    }
}
