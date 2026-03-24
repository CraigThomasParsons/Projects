<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SyncRemoteConversations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conversations:sync-remote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pulls latest conversations directly from ChatGPT and imports them into ChatProjects.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting Automated Pipeline Phase 1: ChatGPT Conversation Sync");
        Log::info("Running scheduled conversations:sync-remote");

        // 1. Gather Known UUIDs to implement difference checking (delta syncing)
        $knownUuids = Conversation::pluck('share_url')
            ->map(function ($url) {
                // Ensure we get just the UUID from the https://chatgpt.com/c/{uuid}
                return basename($url);
            })
            ->filter()
            ->values()
            ->toArray();

        $tempFile = storage_path('app/known_chatgpt_uuids.json');
        File::put($tempFile, json_encode($knownUuids));

        // 2. Prepare Inbox outbox_dir
        $inboxPath = storage_path('app/chatgpt_sync_inbox');
        if (!File::exists($inboxPath)) {
            File::makeDirectory($inboxPath, 0755, true);
        } else {
            // Clean it just in case of a previous failure
            File::cleanDirectory($inboxPath);
        }

        // 3. Trigger the headless Python Scraper (uses curl_cffi to bypass Cloudflare)
        $this->info("Triggering python scraping bridge...");
        
        $pythonScript = base_path('bin/sync_conversations.py');
        $venvPython = '/home/craigpar/Code/ChatGptToChatProjectsBridge/venv/bin/python';
        
        $command = escapeshellcmd("{$venvPython} {$pythonScript} {$tempFile} {$inboxPath}");
        
        $output = [];
        $resultCode = 0;
        exec($command, $output, $resultCode);

        // Display underlying python logs
        foreach ($output as $line) {
            $this->line("  > " . $line);
            Log::info("sync_conversations.py: {$line}");
        }

        if ($resultCode !== 0) {
            $this->error("Python scraper script failed to complete.");
            Log::error("conversations:sync-remote failed during python execution.");
            return static::FAILURE;
        }

        // 4. Import the freshly downloaded Inbox contents via existing robust import command
        $folders = File::directories($inboxPath);
        if (count($folders) === 0) {
            $this->info("No new conversations were found. System is fully synchronized.");
            return static::SUCCESS;
        }

        $this->info("Importing " . count($folders) . " new conversations into the database...");
        
        $this->call('conversations:import-local', [
            'directory' => $inboxPath,
            '--delete' => true
        ]);

        $this->info("Synchronization Complete.");
        return static::SUCCESS;
    }
}
