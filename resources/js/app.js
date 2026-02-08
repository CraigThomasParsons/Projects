import './bootstrap';
import $ from 'jquery';

// Make jQuery global first
window.$ = $;
window.jQuery = $;

// Import Foundation AFTER jQuery is global (just in case)
import 'foundation-sites';

// Initialize Foundation
$(document).ready(function () {
    console.log('Initializing Foundation...');
    $(document).foundation();
    console.log('Foundation initialized.');
});

document.addEventListener('livewire:initialized', () => {
    if (window.Livewire) {
        Livewire.on('close-project-modal', () => {
            $('#add-project-modal').foundation('close');
        });
    }
});
