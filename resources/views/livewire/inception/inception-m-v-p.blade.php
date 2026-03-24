<div class="project-page-container">
    <div class="page-header flex justify-between items-center p-2 mb-4">
        <div>
            <a href="{{ route('projects.inception.wizard', $project) }}" class="button secondary hollow">
                &larr; Abort Inception
            </a>
            <a href="{{ route('projects.inception.features', $project) }}" class="button primary hollow" style="margin-left: 10px;">
                &uarr; Back to Phase 3
            </a>
        </div>
        <div style="color: #a0a0a0; font-weight: bold;">
            Phase 4 of 4: MVP Canvas
        </div>
    </div>

    <div class="grid-container full">
        <div class="grid-x grid-margin-x justify-center">
            <div class="cell large-11">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="h2 text-glow mb-0">The MVP Canvas</h1>
                    <button wire:click="finalizeInception" class="button success large mb-0" style="box-shadow: 0 4px 15px rgba(72, 187, 120, 0.4);">
                        Produce Artifacts &amp; Generate Backlog &rarr;
                    </button>
                </div>
                
                @if (session()->has('success'))
                    <div class="callout success">
                        {{ session('success') }}
                    </div>
                @endif
                
                <p class="lead" style="color: #a0a0a0; margin-bottom: 2rem;">
                    Categorize the feature pool to define exactly what goes into the Minimum Viable Product.
                </p>

                <!-- Unassigned Feature Pool -->
                @if(count($unassignedFeatures) > 0)
                <div class="callout secondary mb-4" style="background: rgba(30,30,30, 0.5); border: 2px dashed #a0a0a0;">
                    <h4 style="color: #a0a0a0;">Unassigned Pipeline Backlog <span class="badge secondary">{{ count($unassignedFeatures) }}</span></h4>
                    <div class="grid-x grid-margin-x">
                        @foreach($unassignedFeatures as $f)
                        <div class="cell medium-4 mb-2">
                            <div class="callout" style="background: rgba(43,44,46, 0.8); border: 1px solid #4a5568;">
                                <div style="font-weight: bold; color: #fff;">{{ $f->title }}</div>
                                <div style="font-size: 0.8rem; color: #ed8936; margin-bottom: 0.5rem;">Score: {{ number_format($f->value_score / max(1, $f->effort_score), 1) }} (V:{{$f->value_score}} E:{{$f->effort_score}})</div>
                                
                                <div class="button-group tiny mb-0">
                                    <button wire:click="assignStatus({{$f->id}}, 'Must Have')" class="button" style="background: #48bb78;">Must</button>
                                    <button wire:click="assignStatus({{$f->id}}, 'Should Have')" class="button" style="background: #4299e1;">Should</button>
                                    <button wire:click="assignStatus({{$f->id}}, 'Could Have')" class="button" style="background: #ed8936;">Could</button>
                                    <button wire:click="assignStatus({{$f->id}}, 'Won\'t Have')" class="button" style="background: #fc8181;">Won't</button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- MoSCoW Quadrants -->
                <div class="grid-x grid-margin-x">
                    <!-- Must Have -->
                    <div class="cell medium-6 mb-4">
                        <div class="callout secondary h-100" style="background: rgba(30, 30, 30, 0.6); border-top: 4px solid #48bb78; min-height: 300px;">
                            <h4 style="color: #48bb78; margin-bottom: 1rem;">Must Have</h4>
                            @foreach($mustHave as $f)
                            <div class="callout mb-2 flex justify-between items-center" style="background: rgba(43,44,46, 0.8); border: 1px solid #2f855a; padding: 0.5rem;">
                                <div style="color: #fff; line-height: 1.2;">
                                    <strong>{{ $f->title }}</strong><br>
                                    <span style="font-size: 0.75rem; color: #a0a0a0;">Score: {{ number_format($f->value_score / max(1, $f->effort_score), 1) }}</span>
                                </div>
                                <button wire:click="assignStatus({{$f->id}}, 'null')" class="button tiny alert hollow mb-0" style="padding: 0.25rem 0.5rem;">Remove</button>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Should Have -->
                    <div class="cell medium-6 mb-4">
                        <div class="callout secondary h-100" style="background: rgba(30, 30, 30, 0.6); border-top: 4px solid #4299e1; min-height: 300px;">
                            <h4 style="color: #4299e1; margin-bottom: 1rem;">Should Have</h4>
                            @foreach($shouldHave as $f)
                            <div class="callout mb-2 flex justify-between items-center" style="background: rgba(43,44,46, 0.8); border: 1px solid #2b6cb0; padding: 0.5rem;">
                                <div style="color: #fff; line-height: 1.2;">
                                    <strong>{{ $f->title }}</strong><br>
                                    <span style="font-size: 0.75rem; color: #a0a0a0;">Score: {{ number_format($f->value_score / max(1, $f->effort_score), 1) }}</span>
                                </div>
                                <button wire:click="assignStatus({{$f->id}}, 'null')" class="button tiny alert hollow mb-0" style="padding: 0.25rem 0.5rem;">Remove</button>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Could Have -->
                    <div class="cell medium-6 mb-4">
                        <div class="callout secondary h-100" style="background: rgba(30, 30, 30, 0.6); border-top: 4px solid #ed8936; min-height: 300px;">
                            <h4 style="color: #ed8936; margin-bottom: 1rem;">Could Have</h4>
                            @foreach($couldHave as $f)
                            <div class="callout mb-2 flex justify-between items-center" style="background: rgba(43,44,46, 0.8); border: 1px solid #c05621; padding: 0.5rem;">
                                <div style="color: #fff; line-height: 1.2;">
                                    <strong>{{ $f->title }}</strong><br>
                                    <span style="font-size: 0.75rem; color: #a0a0a0;">Score: {{ number_format($f->value_score / max(1, $f->effort_score), 1) }}</span>
                                </div>
                                <button wire:click="assignStatus({{$f->id}}, 'null')" class="button tiny alert hollow mb-0" style="padding: 0.25rem 0.5rem;">Remove</button>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Won't Have -->
                    <div class="cell medium-6 mb-4">
                        <div class="callout secondary h-100" style="background: rgba(30, 30, 30, 0.6); border-top: 4px solid #fc8181; min-height: 300px;">
                            <h4 style="color: #fc8181; margin-bottom: 1rem;">Won't Have (This Version)</h4>
                            @foreach($wontHave as $f)
                            <div class="callout mb-2 flex justify-between items-center" style="background: rgba(43,44,46, 0.8); border: 1px solid #c53030; padding: 0.5rem;">
                                <div style="color: #fff; line-height: 1.2;">
                                    <strong>{{ $f->title }}</strong><br>
                                    <span style="font-size: 0.75rem; color: #a0a0a0;">Score: {{ number_format($f->value_score / max(1, $f->effort_score), 1) }}</span>
                                </div>
                                <button wire:click="assignStatus({{$f->id}}, 'null')" class="button tiny alert hollow mb-0" style="padding: 0.25rem 0.5rem;">Remove</button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
