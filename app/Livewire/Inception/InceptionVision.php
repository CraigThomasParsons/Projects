<?php

namespace App\Livewire\Inception;

use App\Models\Project;
use App\Models\Inception;
use Livewire\Component;

class InceptionVision extends Component
{
    public Project $project;
    public Inception $inception;

    public $vision_statement;
    public $business_goals;
    public $success_metrics;

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->inception = $project->inceptions()->firstOrCreate([], ['status' => 'vision']);
        
        $this->vision_statement = $this->inception->vision_statement;
        $this->business_goals = $this->inception->business_goals;
        $this->success_metrics = $this->inception->success_metrics;
    }

    public function saveAndNext()
    {
        $this->validate([
            'vision_statement' => 'required|string',
            'business_goals' => 'nullable|string',
            'success_metrics' => 'nullable|string',
        ]);

        $this->inception->update([
            'vision_statement' => $this->vision_statement,
            'business_goals' => $this->business_goals,
            'success_metrics' => $this->success_metrics,
            'status' => 'personas'
        ]);

        return redirect()->route('projects.inception.personas', $this->project);
    }

    public function render()
    {
        return view('livewire.inception.inception-vision')->layout('layouts.app');
    }
}
