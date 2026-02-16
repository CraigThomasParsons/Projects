<?php

use App\Http\Controllers\Api\ManualConversationController;
use App\Http\Controllers\Api\PiperProjectInputController;
use App\Http\Controllers\ConversationImportController;
use Illuminate\Support\Facades\Route;

// Import canonical conversation records from shared links.
Route::post('/conversations/{conversation}/import', [ConversationImportController::class, 'store']);

// Return normalized project input payload for Piper extraction workflows.
Route::get('/projects/{project}/piper-input', [PiperProjectInputController::class, 'show']);

// Persist manually pasted transcripts when share-link sync is unavailable.
Route::post('/projects/{project}/conversations/paste', [ManualConversationController::class, 'store']);
