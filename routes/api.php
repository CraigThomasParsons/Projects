<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConversationImportController;

Route::post('/conversations/{conversation}/import', [ConversationImportController::class, 'store']);
