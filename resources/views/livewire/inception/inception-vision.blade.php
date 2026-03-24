<div class="project-page-container">
    <div class="page-header flex justify-between items-center p-2 mb-4">
        <div>
            <a href="{{ route('projects.inception.wizard', $project) }}" class="button secondary hollow">
                &larr; Abort Inception
            </a>
        </div>
        <div style="color: #a0a0a0; font-weight: bold;">
            Phase 1 of 4: Product Vision
        </div>
    </div>

    <div class="grid-container">
        <div class="grid-x grid-margin-x justify-center">
            <div class="cell large-8">
                <h1 class="h2 text-glow mb-4">Define the Vision</h1>
                <p class="lead" style="color: #a0a0a0; margin-bottom: 2rem;">
                    For whom is this product? What problem does it solve? What is the outcome? Why now?
                </p>

                <form wire:submit.prevent="saveAndNext">
                    <div class="callout secondary" style="background: rgba(30, 30, 30, 0.6); padding: 1.5rem; margin-bottom: 1.5rem;">
                        <label style="color: #63b3ed; font-weight: bold; font-size: 1.1rem; margin-bottom: 0.5rem;">
                            The Vision Statement
                            <p class="help-text" style="color:#d0d0d0; font-weight:normal;">Create a concise elevating pitch. "For [target customer] who [statement of the need/opportunity], the [product name] is a [product category] that [key benefit, reason to buy]."</p>
                            <textarea wire:model="vision_statement" rows="5" placeholder="For the modern developer who suffers from task overflow..."></textarea>
                        </label>
                        @error('vision_statement') <span class="form-error is-visible">{{ $message }}</span> @enderror
                    </div>

                    <div class="callout secondary" style="background: rgba(30, 30, 30, 0.6); padding: 1.5rem; margin-bottom: 1.5rem;">
                        <label style="color: #63b3ed; font-weight: bold; font-size: 1.1rem; margin-bottom: 0.5rem;">
                            Business Goals (Optional)
                            <p class="help-text" style="color:#d0d0d0; font-weight:normal;">What are the 3 main objectives you are trying to reach with this product?</p>
                            <textarea wire:model="business_goals" rows="4" placeholder="1. Save 20 hours a week on boilerplate..."></textarea>
                        </label>
                    </div>

                    <div class="callout secondary" style="background: rgba(30, 30, 30, 0.6); padding: 1.5rem; margin-bottom: 2rem;">
                        <label style="color: #63b3ed; font-weight: bold; font-size: 1.1rem; margin-bottom: 0.5rem;">
                            Success Metrics (Optional)
                            <p class="help-text" style="color:#d0d0d0; font-weight:normal;">How will you measure if this was a success?</p>
                            <textarea wire:model="success_metrics" rows="3" placeholder="Time to first commit reduced to 5 mins..."></textarea>
                        </label>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="button primary large" style="box-shadow: 0 4px 15px rgba(66, 153, 225, 0.4);">
                            Save &amp; Continue to Phase 2 &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
