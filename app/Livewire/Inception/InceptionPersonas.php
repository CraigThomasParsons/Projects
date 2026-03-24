<?php

namespace App\Livewire\Inception;

use App\Models\Project;
use App\Models\Inception;
use Livewire\Component;

class InceptionPersonas extends Component
{
    public Project $project;
    public Inception $inception;
    public $savedPersonas = [];

    // Form binding
    public $name = '';
    public $goals = '';
    public $frustrations = '';
    public $context = '';
    public $tech_level = '';

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->inception = $project->inceptions()->firstOrCreate([], ['status' => 'vision']);
        $this->refreshPersonas();
    }

    public function refreshPersonas()
    {
        $this->savedPersonas = $this->inception->personas()->get();
    }

    public function addPersona()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'goals' => 'nullable|string',
            'frustrations' => 'nullable|string',
            'context' => 'nullable|string',
            'tech_level' => 'nullable|string',
        ]);

        $this->inception->personas()->create([
            'name' => $this->name,
            'goals' => $this->goals,
            'frustrations' => $this->frustrations,
            'context' => $this->context,
            'tech_level' => $this->tech_level,
        ]);

        $this->reset(['name', 'goals', 'frustrations', 'context', 'tech_level']);
        $this->refreshPersonas();
    }

    public function removePersona($id)
    {
        $this->inception->personas()->where('id', $id)->delete();
        $this->refreshPersonas();
    }

    public function nextPhase()
    {
        $this->inception->update(['status' => 'features']);
        return redirect()->route('projects.inception.features', $this->project);
    }

    public function render()
    {
        return view('livewire.inception.inception-personas')->layout('layouts.app');
    }
}
