<?php

namespace App\Livewire\Inception;

use App\Models\Project;
use App\Models\Inception;
use Livewire\Component;

class InceptionFeatures extends Component
{
    public Project $project;
    public Inception $inception;
    public $savedFeatures = [];

    // Form binding
    public $title = '';
    public $description = '';
    public $value_score = 5;
    public $effort_score = 5;

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->inception = $project->inceptions()->firstOrCreate([], ['status' => 'vision']);
        $this->refreshFeatures();
    }

    public function refreshFeatures()
    {
        $this->savedFeatures = $this->inception->features()->get();
    }

    public function addFeature()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value_score' => 'required|integer|min:1|max:10',
            'effort_score' => 'required|integer|min:1|max:10',
        ]);

        $this->inception->features()->create([
            'title' => $this->title,
            'description' => $this->description,
            'value_score' => $this->value_score,
            'effort_score' => $this->effort_score,
            'mvp_status' => null, // Left null until MVP Canvas phase
        ]);

        $this->reset(['title', 'description', 'value_score', 'effort_score']);
        $this->value_score = 5;
        $this->effort_score = 5;
        $this->refreshFeatures();
    }

    public function removeFeature($id)
    {
        $this->inception->features()->where('id', $id)->delete();
        $this->refreshFeatures();
    }

    public function nextPhase()
    {
        $this->inception->update(['status' => 'mvp']);
        return redirect()->route('projects.inception.mvp', $this->project);
    }

    public function render()
    {
        return view('livewire.inception.inception-features')->layout('layouts.app');
    }
}
