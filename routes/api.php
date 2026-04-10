<?php

use App\Http\Controllers\Api\ManualConversationController;
use App\Http\Controllers\Api\PiperProjectInputController;
use App\Http\Controllers\Api\KraxInputController;
use App\Http\Controllers\Api\ProjectRegistryController;
use App\Http\Controllers\ConversationImportController;
use Illuminate\Support\Facades\Route;

// Import canonical conversation records from shared links.
Route::post('/conversations/{conversation}/import', [ConversationImportController::class, 'store']);

// Return normalized project input payload for Piper extraction workflows.
Route::get('/projects/{project}/piper-input', [PiperProjectInputController::class, 'show']);

// Return heavy contextual payload containing core Lean Inception artifacts + conversations
Route::get('/projects/{project}/krax-input', [KraxInputController::class, 'show']);

// Return canonical project records for downstream projections.
Route::get('/projects', [ProjectRegistryController::class, 'index']);

// Return one canonical project record by numeric id or UUID.
Route::get('/projects/{projectIdentifier}', [ProjectRegistryController::class, 'show'])
	->where('projectIdentifier', '[0-9a-fA-F\-]+');

// Persist manually pasted transcripts when share-link sync is unavailable.
Route::post('/projects/{project}/conversations/paste', [ManualConversationController::class, 'store']);
