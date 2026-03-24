<div class="project-page-container">
    <div class="page-header flex justify-between items-center p-2 mb-4">
        <div>
            <a href="{{ route('projects.show', $project) }}" class="button secondary hollow">
                &larr; Back to {{ $project->name }}
            </a>
        </div>
    </div>

    <div class="grid-container">
        <div class="grid-x grid-margin-x">
            <div class="cell text-center mb-4">
                <h1 class="h1 text-glow" style="margin-bottom: 0.5rem;">Lean Inception Machine</h1>
                <p class="lead" style="color: #a0a0a0;">Transform your vague ideas into a structured AutoPipeline backlog.</p>
            </div>

            <div class="cell">
                <div class="callout secondary" style="border-radius: 8px; background: rgba(30, 30, 30, 0.6); padding: 2rem;">
                    <h3 class="h4" style="color: #63b3ed; margin-bottom: 1.5rem;">The Guided Product Direction Engine</h3>
                    
                    <div class="grid-x grid-margin-x" style="margin-bottom: 1.5rem;">
                        <div class="cell medium-3 text-center">
                            <div style="font-size: 2rem; color: #48bb78; margin-bottom: 0.5rem;">1</div>
                            <strong>Product Vision</strong>
                            <p class="help-text">Define the problem and outcome.</p>
                        </div>
                        <div class="cell medium-3 text-center">
                            <div style="font-size: 2rem; color: #48bb78; margin-bottom: 0.5rem;">2</div>
                            <strong>Target Personas</strong>
                            <p class="help-text">Who are we building this for?</p>
                        </div>
                        <div class="cell medium-3 text-center">
                            <div style="font-size: 2rem; color: #48bb78; margin-bottom: 0.5rem;">3</div>
                            <strong>Features Brainstorm</strong>
                            <p class="help-text">Score ideas by Value & Effort.</p>
                        </div>
                        <div class="cell medium-3 text-center">
                            <div style="font-size: 2rem; color: #48bb78; margin-bottom: 0.5rem;">4</div>
                            <strong>MVP Canvas</strong>
                            <p class="help-text">Generate local Markdown & Backlogs.</p>
                        </div>
                    </div>
                </div>

                <div class="text-center" style="margin-top: 3rem; margin-bottom: 3rem;">
                    <a href="{{ route('projects.inception.vision', $project) }}" class="button primary large" style="box-shadow: 0 4px 15px rgba(66, 153, 225, 0.4); padding: 1rem 3rem; font-size: 1.25rem; border-radius: 4px;">
                        Begin Phase 1: Product Vision &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
