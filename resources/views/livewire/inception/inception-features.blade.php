<div class="project-page-container">
    <div class="page-header flex justify-between items-center p-2 mb-4">
        <div>
            <a href="{{ route('projects.inception.wizard', $project) }}" class="button secondary hollow">
                &larr; Abort Inception
            </a>
            <a href="{{ route('projects.inception.personas', $project) }}" class="button primary hollow" style="margin-left: 10px;">
                &uarr; Back to Phase 2
            </a>
        </div>
        <div style="color: #a0a0a0; font-weight: bold;">
            Phase 3 of 4: Features
        </div>
    </div>

    <div class="grid-container">
        <div class="grid-x grid-margin-x justify-center">
            <div class="cell large-10">
                <h1 class="h2 text-glow mb-4">Features Brainstorm</h1>
                <p class="lead" style="color: #a0a0a0; margin-bottom: 2rem;">
                    List off ideas, big and small. Score their perceived Value to the customer (1-10) and anticipated Effort to build (1-10).
                </p>

                <div class="grid-x grid-margin-x mb-4">
                    <div class="cell medium-5">
                        <div class="callout secondary" style="background: rgba(30, 30, 30, 0.6); padding: 1.5rem; border-top: 3px solid #ed8936;">
                            <h4 style="color: #ed8936; margin-bottom: 1rem;">Add a Feature</h4>
                            <form wire:submit.prevent="addFeature">
                                
                                <div class="mb-2">
                                    <label style="font-weight: bold; color: #d0d0d0;">Feature Title
                                        <input type="text" wire:model="title" placeholder="e.g. 'One-Click Deploy to AWS'">
                                    </label>
                                    @error('title') <span class="form-error is-visible">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-3">
                                    <label style="font-weight: bold; color: #a0a0a0;">Short Description
                                        <textarea wire:model="description" rows="2" placeholder="Briefly explain what this means..."></textarea>
                                    </label>
                                </div>

                                <div class="grid-x grid-margin-x mb-3">
                                    <div class="cell medium-6">
                                        <label style="font-weight: bold; color: #48bb78;">Business Value (1-10)
                                            <input type="number" wire:model="value_score" min="1" max="10">
                                        </label>
                                    </div>
                                    <div class="cell medium-6">
                                        <label style="font-weight: bold; color: #fc8181;">Dev Effort (1-10)
                                            <input type="number" wire:model="effort_score" min="1" max="10">
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="button secondary mt-2" style="background: rgba(237, 137, 54, 0.2); border: 1px solid #ed8936; width: 100%;">
                                    + Add Feature Concept
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="cell medium-7">
                        <h4 style="color: #63b3ed; margin-bottom: 1rem;">The Brainstorm Pool</h4>
                        @if(count($savedFeatures) > 0)
                            <div style="max-height: 500px; overflow-y: auto; padding-right: 10px;">
                                @foreach($savedFeatures as $f)
                                <div class="callout secondary" style="background: rgba(43, 44, 46, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 6px; padding: 1rem; position: relative; margin-bottom: 1rem;">
                                    <button wire:click="removeFeature({{$f->id}})" class="close-button" aria-label="Dismiss alert" type="button" style="right: 1rem; top: 0.5rem; color: #ff5e5e;">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    
                                    <div class="flex justify-between items-start mb-2">
                                        <h5 style="color: #fff; margin: 0; padding-right: 20px;">{{ $f->title }}</h5>
                                        <div style="font-weight: bold; font-family: monospace;">
                                            <span style="color: #48bb78;">V:{{ $f->value_score }}</span> / 
                                            <span style="color: #fc8181;">E:{{ $f->effort_score }}</span> =
                                            <span style="color: #ed8936;">Score: {{ number_format($f->value_score / max(1, $f->effort_score), 1) }}</span>
                                        </div>
                                    </div>
                                    
                                    @if($f->description)
                                    <div style="color: #a0a0a0; font-size: 0.9rem;">
                                        {{ $f->description }}
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="callout warning" style="background: rgba(221, 107, 32, 0.1); border-color: #dd6b20;">
                                The idea board is empty. Start brainstorming!
                            </div>
                        @endif
                    </div>
                </div>

                <div class="text-right" style="margin-top: 2rem;">
                    <button wire:click="nextPhase" class="button primary large" style="box-shadow: 0 4px 15px rgba(66, 153, 225, 0.4);">
                        Continue to Phase 4 (MVP Definition) &rarr;
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
