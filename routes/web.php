<?php

use App\Livewire\ConversationTranscriptPage;
use App\Livewire\ProjectConversationsPage;
use App\Livewire\ProjectsPage;
use App\Livewire\RegistryEditorPage;
use Illuminate\Support\Facades\Route;

Route::get('/', ProjectsPage::class)->name('projects.index');
Route::get('/registry', RegistryEditorPage::class)->name('registry');
Route::get('/projects/{project}', ProjectConversationsPage::class)->name('projects.show');
Route::get('/projects/{project}/conversations/{conversation}', ConversationTranscriptPage::class)
    ->name('conversations.show');

Route::prefix('/projects/{project}/inception')->group(function () {
    Route::get('/', \App\Livewire\Inception\InceptionWizard::class)->name('projects.inception.wizard');
    Route::get('/vision', \App\Livewire\Inception\InceptionVision::class)->name('projects.inception.vision');
    Route::get('/personas', \App\Livewire\Inception\InceptionPersonas::class)->name('projects.inception.personas');
    Route::get('/features', \App\Livewire\Inception\InceptionFeatures::class)->name('projects.inception.features');
    Route::get('/mvp', \App\Livewire\Inception\InceptionMVP::class)->name('projects.inception.mvp');
});

Route::get('/preferences', \App\Livewire\Preferences::class)->name('preferences');

Route::get('/team', [\App\Http\Controllers\TeamMemberController::class, 'index'])->name('team.index');
Route::get('/team/{teamMember}', [\App\Http\Controllers\TeamMemberController::class, 'show'])->name('team.show');
Route::post('/team/{teamMember}/upload', [\App\Http\Controllers\TeamMemberController::class, 'uploadImage'])->name('team.upload');

Route::get('/debug-drivers', function () {
    return [
        'available_drivers' => PDO::getAvailableDrivers(),
        'loaded_extensions' => get_loaded_extensions(),
        'env_connection' => config('database.default'),
        'db_connection' => env('DB_CONNECTION'),
        'php_sapi' => php_sapi_name(),
    ];
});
