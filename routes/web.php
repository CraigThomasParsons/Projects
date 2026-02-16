<?php

use App\Livewire\ConversationTranscriptPage;
use App\Livewire\ProjectConversationsPage;
use App\Livewire\ProjectsPage;
use Illuminate\Support\Facades\Route;

Route::get('/', ProjectsPage::class)->name('projects.index');
Route::get('/projects/{project}', ProjectConversationsPage::class)->name('projects.show');
Route::get('/projects/{project}/conversations/{conversation}', ConversationTranscriptPage::class)
    ->name('conversations.show');

Route::get('/debug-drivers', function () {
    return [
        'available_drivers' => PDO::getAvailableDrivers(),
        'loaded_extensions' => get_loaded_extensions(),
        'env_connection' => config('database.default'),
        'db_connection' => env('DB_CONNECTION'),
        'php_sapi' => php_sapi_name(),
    ];
});
