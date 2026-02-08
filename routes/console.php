<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('piper:token', function () {
    $token = Str::random(64);
    $envPath = base_path('.env');

    if (!file_exists($envPath)) {
        $this->error('.env file not found.');
        return 1;
    }

    $contents = file_get_contents($envPath);
    $line = "PIPER_TOKEN={$token}";

    if (preg_match('/^PIPER_TOKEN=.*$/m', $contents)) {
        $contents = preg_replace('/^PIPER_TOKEN=.*$/m', $line, $contents);
    } else {
        $contents = rtrim($contents).PHP_EOL.$line.PHP_EOL;
    }

    file_put_contents($envPath, $contents);

    $this->info('PIPER_TOKEN generated and saved to .env');
    return 0;
})->purpose('Generate and persist the Piper API token');
