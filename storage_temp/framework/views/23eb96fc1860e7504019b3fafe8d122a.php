<div class="grid-container">
    <div class="grid-x grid-margin-x align-middle">
        <div class="cell auto">
            <h1 class="h2"><?php echo e($project->name); ?></h1>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($project->description): ?>
                <p class="subheader"><?php echo e($project->description); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="cell shrink text-right">
            <a
                href="<?php echo e(route('projects.index')); ?>"
                class="button hollow secondary small"
            >
                &#x2190; Back to Projects
            </a>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="callout success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(session()->has('error')): ?>
        <div class="callout alert">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid-x grid-margin-x align-middle">
        <div class="cell auto">
            <h2 class="h4">Conversations</h2>
        </div>
        <div class="cell shrink text-right">
            <button
                wire:click="openConversationModal"
                class="button primary"
            >
                + New Conversation
            </button>
        </div>
    </div>

    <ul class="no-bullet">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conversation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="callout secondary">
                <div class="grid-x grid-margin-x align-middle">
                    <div class="cell auto">
                        <strong><?php echo e($conversation->title ?? 'Untitled Conversation'); ?></strong>
                        <div class="subheader">
                            Share: <a href="<?php echo e($conversation->share_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo e($conversation->share_url); ?></a>
                        </div>
                        <div class="subheader">Updated <?php echo e($conversation->updated_at->diffForHumans()); ?></div>
                    </div>
                    <div class="cell shrink">
                        <button
                            wire:click="syncConversation(<?php echo e($conversation->id); ?>)"
                            class="button secondary"
                        >
                            Sync
                        </button>
                    </div>
                </div>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="text-muted">No conversations yet. Add one to start.</li>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </ul>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showConversationModal): ?>
        <div class="reveal-overlay" style="display: block;">
            <div
                class="reveal small"
                style="display: block;"
                role="dialog"
                aria-modal="true"
                wire:keydown.escape="closeConversationModal"
            >
                <h2>Add Conversation</h2>

                <label>ChatGPT Share URL
                    <input
                        type="text"
                        wire:model.defer="shareUrl"
                        placeholder="https://chatgpt.com/share/..."
                    />
                </label>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['shareUrl'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="form-error is-visible"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="button-group align-right">
                    <button
                        wire:click="closeConversationModal"
                        class="button secondary hollow"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="saveConversation"
                        class="button primary"
                    >
                        Save
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/craigpar/Code/ChatProjects/resources/views/livewire/project-conversations-page.blade.php ENDPATH**/ ?>