<?php
use Illuminate\Support\Facades\Route;

Route::get('/debug-drivers', function () {
    return [
        'available_drivers' => PDO::getAvailableDrivers(),
        'loaded_extensions' => get_loaded_extensions(),
        'env_connection' => config('database.default'),
        'db_connection' => env('DB_CONNECTION'),
    ];
});
