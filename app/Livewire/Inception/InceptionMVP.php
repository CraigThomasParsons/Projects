<?php

namespace App\Livewire\Inception;

use App\Models\Project;
use App\Models\Inception;
use Livewire\Component;

class InceptionMVP extends Component
{
    public Project $project;
    public Inception $inception;
    
    // Feature Groups
    public $unassignedFeatures = [];
    public $mustHave = [];
    public $shouldHave = [];
    public $couldHave = [];
    public $wontHave = [];

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->inception = $project->inceptions()->firstOrCreate([], ['status' => 'vision']);
        $this->refreshCanvas();
    }

    public function refreshCanvas()
    {
        $allFeatures = $this->inception->features()->get();
        
        $this->unassignedFeatures = $allFeatures->where('mvp_status', null);
        $this->mustHave = $allFeatures->where('mvp_status', 'Must Have');
        $this->shouldHave = $allFeatures->where('mvp_status', 'Should Have');
        $this->couldHave = $allFeatures->where('mvp_status', 'Could Have');
        $this->wontHave = $allFeatures->where('mvp_status', 'Won\'t Have');
    }

    public function assignStatus($featureId, $status)
    {
        if ($status === 'null') {
            $status = null;
        }
        $this->inception->features()->where('id', $featureId)->update(['mvp_status' => $status]);
        $this->refreshCanvas();
    }

    public function finalizeInception(\App\Services\InceptionArtifactGenerator $generator)
    {
        $this->inception->update(['status' => 'completed']);
        
        // Generate physical markdown artifacts inside the project's inception/ folder
        $generator->generate($this->inception);
        
        // Trigger the asynchronous Sprint Generation engine using the Piper proxy logic.
        \App\Events\InceptionCompleted::dispatch($this->inception);
        
        session()->flash('success', 'Inception Finalized! Autonomous Sprint generation is running in the background.');
        
        return redirect()->route('projects.show', $this->project);
    }

    public function render()
    {
        return view('livewire.inception.inception-m-v-p')->layout('layouts.app');
    }
}
