<div class="project-page-container">
    <div class="page-header flex justify-between items-center p-2 mb-4">
        <div>
            <a href="{{ route('projects.inception.wizard', $project) }}" class="button secondary hollow">
                &larr; Abort Inception
            </a>
            <a href="{{ route('projects.inception.vision', $project) }}" class="button primary hollow" style="margin-left: 10px;">
                &uarr; Back to Phase 1
            </a>
        </div>
        <div style="color: #a0a0a0; font-weight: bold;">
            Phase 2 of 4: Personas
        </div>
    </div>

    <div class="grid-container">
        <div class="grid-x grid-margin-x justify-center">
            <div class="cell large-10">
                <h1 class="h2 text-glow mb-4">Target Personas</h1>
                <p class="lead" style="color: #a0a0a0; margin-bottom: 2rem;">
                    Who are we building this for? Define the primary users and buyers.
                </p>

                @if(count($savedPersonas) > 0)
                    <div class="grid-x grid-margin-x mb-4">
                        @foreach($savedPersonas as $p)
                        <div class="cell medium-6 mb-4">
                            <div class="callout secondary" style="background: rgba(43, 44, 46, 0.8); border: 1px solid #48bb78; border-radius: 6px; padding: 1rem; position: relative;">
                                <button wire:click="removePersona({{$p->id}})" class="close-button" aria-label="Dismiss alert" type="button" style="right: 1rem; top: 0.5rem; color: #ff5e5e;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 style="color: #48bb78; margin-top: 0;">{{ $p->name }}</h4>
                                @if($p->tech_level) <span class="label primary mb-2" style="font-size: 0.75rem;">Tech Level: {{ $p->tech_level }}</span> @endif
                                
                                @if($p->goals)
                                <div><strong style="color: #63b3ed;">Goals:</strong> {{ Str::limit($p->goals, 100) }}</div>
                                @endif
                                @if($p->frustrations)
                                <div><strong style="color: #fc8181;">Frustrations:</strong> {{ Str::limit($p->frustrations, 100) }}</div>
                                @endif
                                @if($p->context)
                                <div><strong style="color: #a0a0a0;">Context:</strong> {{ Str::limit($p->context, 100) }}</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="callout warning" style="background: rgba(221, 107, 32, 0.1); border-color: #dd6b20;">
                        No Personas have been added yet. Defining at least one persona will dramatically improve story generation!
                    </div>
                @endif

                <div class="callout secondary" style="background: rgba(30, 30, 30, 0.6); padding: 1.5rem; margin-bottom: 2rem; border-top: 3px solid #63b3ed;">
                    <h4 style="color: #63b3ed; margin-bottom: 1rem;">Add a New Persona</h4>
                    <form wire:submit.prevent="addPersona">
                        
                        <div class="grid-x grid-margin-x">
                            <div class="cell medium-6 mb-2">
                                <label style="font-weight: bold; color: #d0d0d0;">Name / Role
                                    <input type="text" wire:model="name" placeholder="e.g. 'Jane the CTO' or 'Impatient Gamer'">
                                </label>
                                @error('name') <span class="form-error is-visible">{{ $message }}</span> @enderror
                            </div>
                            <div class="cell medium-6 mb-2">
                                <label style="font-weight: bold; color: #d0d0d0;">Tech Level
                                    <input type="text" wire:model="tech_level" placeholder="High, Medium, Low, or 'Doesn't know what API means'">
                                </label>
                            </div>
                        </div>

                        <div class="grid-x grid-margin-x">
                            <div class="cell medium-6 mb-2">
                                <label style="font-weight: bold; color: #63b3ed;">Their Goals
                                    <textarea wire:model="goals" rows="3" placeholder="What does this person want to achieve?"></textarea>
                                </label>
                            </div>
                            <div class="cell medium-6 mb-2">
                                <label style="font-weight: bold; color: #fc8181;">Their Frustrations
                                    <textarea wire:model="frustrations" rows="3" placeholder="What annoys them about their current process?"></textarea>
                                </label>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label style="font-weight: bold; color: #d0d0d0;">Overall Context
                                <textarea wire:model="context" rows="2" placeholder="When and where are they using this tool?"></textarea>
                            </label>
                        </div>

                        <button type="submit" class="button secondary mt-2" style="background: rgba(66, 153, 225, 0.2); border: 1px solid #4299e1;">
                            + Add Persona
                        </button>
                    </form>
                </div>

                <div class="text-right">
                    <button wire:click="nextPhase" class="button primary large" style="box-shadow: 0 4px 15px rgba(66, 153, 225, 0.4);">
                        Continue to Phase 3 &rarr;
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
