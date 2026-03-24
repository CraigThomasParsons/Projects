<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\Project;
use App\Models\ProjectAlias;
use App\Services\ChatGptShareImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportLocalConversations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conversations:import-local {directory} {--delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports local ChatGPT extraction packages from a specified inbox folder.';

    /**
     * Execute the console command.
     */
    public function handle(ChatGptShareImporter $importer)
    {
        $directory = $this->argument('directory');

        if (!File::isDirectory($directory)) {
            $this->error("Directory not found: {$directory}");
            return Command::FAILURE;
        }

        // Find all conversation.json and project.json files recursively
        $conversations = File::allFiles($directory);
        $packageDirs = [];

        foreach ($conversations as $file) {
            $filename = $file->getFilename();
            if ($filename === 'conversation.json' || $filename === 'project.json') {
                $packageDirs[$file->getPath()] = $filename;
            }
        }
        
        if (empty($packageDirs)) {
            $this->info("No packages found in {$directory}");
            return Command::SUCCESS;
        }

        foreach ($packageDirs as $packageDir => $payloadFilename) {
            $this->info("Processing package: " . basename($packageDir));
            
            $jsonFile = $packageDir . '/conversation.json';
            $contextFile = $packageDir . '/context.md';
            
            // Attempt to guess the project from the Context artifact or folder name
            $project = $this->guessProject($contextFile, $packageDir);
            
            if (!$project) {
                // Final Fallback: The Unassigned Project
                $project = Project::where('name', 'Unassigned')->first();
                if (!$project) {
                    $this->warn("Could not reliably associate a project and Unassigned is missing. Skipping.");
                    continue;
                }
            }

            if (!File::exists($jsonFile)) {
                $this->warn("No conversation.json found. Skipping.");
                continue;
            }

            $payloadData = json_decode(File::get($jsonFile), true);
            
            // Handle array of conversations or single conversation
            $records = is_array($payloadData) && isset($payloadData[0]) && is_array($payloadData[0]) ? $payloadData : [$payloadData];
            
            foreach ($records as $record) {
                $uuid = $record['id'] ?? $record['conversation_id'] ?? null;
                $title = $record['title'] ?? 'Imported Conversation';

                if (!$uuid) {
                    $this->warn("No conversation UUID found in JSON. Skipping record.");
                    continue;
                }

                $shareUrl = "https://chatgpt.com/c/{$uuid}";

                $conversation = Conversation::firstOrNew(['share_url' => $shareUrl]);
                
                $conversation->project_id = $project->id;
                $conversation->title = $title;
                $conversation->source_type = 'local_import';
                
                $markdownDocument = $this->convertRawJsonToMarkdown($title, $record);
                
                $conversation->raw_content = $markdownDocument;
                $conversation->save();

                \App\Events\ConversationImported::dispatch($conversation);

                // Save to file system exactly where requested by user
                $sanitizedProjectName = Str::slug($project->name);
                $sanitizedTitle = Str::slug($title);
                $projectPath = "/home/craigpar/Documents/Projects/{$sanitizedProjectName}";

                if (!File::exists($projectPath)) {
                    File::makeDirectory($projectPath, 0755, true);
                }

                $filePath = "{$projectPath}/{$sanitizedTitle}.md";
                File::put($filePath, $markdownDocument);

                $this->info("Successfully imported '{$title}' -> " . $project->name . " (File: {$filePath})");
            }
            
            if ($this->option('delete')) {
                File::deleteDirectory($packageDir);
                $this->info("Cleaned up package directory: " . basename($packageDir));
            }
        }

        return Command::SUCCESS;
    }

    private function guessProject(string $contextFilePath, string $packageDir): ?Project
    {
        if (File::exists($contextFilePath)) {
            $contents = File::get($contextFilePath);
            
            // Context.md was updated to append: "## Matched Known Project\nName" by the extractor
            if (preg_match('/## Matched Known Project\s+([^\n]+)/i', $contents, $matches)) {
                $projectName = trim($matches[1]);
                if ($projectName !== 'None') {
                    $projectType = 'code'; // Default to code project
                    
                    // Strip the prefix and determine type
                    if (preg_match('/^Ideas:\s*(.+)$/i', $projectName, $typeMatches)) {
                        $projectType = 'idea';
                        $projectName = trim($typeMatches[1]);
                    } elseif (preg_match('/^Code:\s*(.+)$/i', $projectName, $typeMatches)) {
                        $projectType = 'code';
                        $projectName = trim($typeMatches[1]);
                    }

                    // 1. Direct name match
                    $project = Project::where('name', 'ilike', "%{$projectName}%")->first();
                    if ($project !== null) {
                        return $project;
                    }

                    // 2. Alias match
                    $alias = ProjectAlias::where('alias', 'ilike', $projectName)->first();
                    if ($alias !== null) {
                        return $alias->project;
                    }

                    // Auto-create a brand new project record.
                    // Instead of dropping unmatched content into an Unassigned bucket,
                    // we dynamically create a new repository using the exact name
                    // provided by the ChatGPT export.
                    $newlyCreatedProject = new Project();
                    $newlyCreatedProject->name = $projectName;
                    $newlyCreatedProject->type = $projectType;
                    $newlyCreatedProject->save();
                    
                    return $newlyCreatedProject;
                }
            }
            
            // Fallback: Check if there's a loose match in the JSON array of guessed projects
            if (preg_match('/Project Names:\s+(.+)/i', $contents, $matches)) {
                $guessedArrayStr = str_replace(['[', ']', '\'', '"'], '', $matches[1]);
                $guesses = array_map('trim', explode(',', $guessedArrayStr));
                
                foreach ($guesses as $guess) {
                    if ($guess !== 'Unknown' && !empty($guess)) {
                        // 1. Direct name match
                        $project = Project::where('name', 'ilike', "%{$guess}%")->first();
                        if ($project !== null) {
                            return $project;
                        }

                        // 2. Alias match
                        $alias = ProjectAlias::where('alias', 'ilike', $guess)->first();
                        if ($alias !== null) {
                            return $alias->project;
                        }
                    }
                }
            }
        }

        // Fallback: Extract project alias from the folder name (e.g., g-p-hash-code-alias or g-p-hash-alias)
        // Since packageDir might be UUID, we should look at its parent if packageDir doesn't have the g-p prefix
        $dirName = basename($packageDir);
        if (!preg_match('/^g-p-[a-f0-9]{32}-/i', $dirName)) {
            // It's likely the UUID folder, check the parent
            $dirName = basename(dirname($packageDir));
        }

        $projectType = 'code'; // Default
        $folderAlias = null;

        if (preg_match('/^g-p-[a-f0-9]{32}-code-(.+)$/i', $dirName, $matches)) {
            $folderAlias = trim($matches[1]);
            $projectType = 'code';
        } elseif (preg_match('/^g-p-[a-f0-9]{32}-ideas-(.+)$/i', $dirName, $matches)) {
            $folderAlias = trim($matches[1]);
            $projectType = 'idea';
        } elseif (preg_match('/^g-p-[a-f0-9]{32}-(.+)$/i', $dirName, $matches)) {
            $folderAlias = trim($matches[1]);
            $projectType = 'code';
        }

        if ($folderAlias !== null) {
            // 1. Direct name match (exact or containing with spaces)
            $spacedAlias = str_replace('-', ' ', $folderAlias);
            $project = Project::where('name', 'ilike', "%{$spacedAlias}%")->first();
            if ($project !== null) {
                return $project;
            }

            // 2. Alias match
            $alias = ProjectAlias::where('alias', 'ilike', $folderAlias)->first();
            if ($alias !== null) {
                return $alias->project;
            }

            // 3. Slug match (check if any project's slug is a substring of the folder alias)
            $allProjects = Project::all();
            foreach ($allProjects as $iteratedProject) {
                $projectSlug = \Illuminate\Support\Str::slug($iteratedProject->name);
                if (!empty($projectSlug) && str_contains($folderAlias, $projectSlug)) {
                    return $iteratedProject;
                }
            }

            // Auto-create a new project from the slugified folder name.
            // When all fuzzy matching strategies fail, we generate a title-cased
            // project name and create the database record automatically.
            $capitalizedProjectName = ucwords($spacedAlias);
            
            $newlyCreatedProject = new Project();
            $newlyCreatedProject->name = $capitalizedProjectName;
            $newlyCreatedProject->type = $projectType;
            $newlyCreatedProject->save();

            return $newlyCreatedProject;
        }

        // Final Fallback: The Unassigned Project
        // We guarantee a fallback payload exists so the conversation is safely retained.
        return Project::firstOrCreate(['name' => 'Unassigned']);
    }

    private function convertRawJsonToMarkdown(string $title, array $conversationData): string
    {
        $markdown = "# {$title}\n\n";
        
        $mapping = $conversationData['mapping'] ?? [];
        if (empty($mapping)) {
            return $markdown . "No message data found in JSON payload.\n";
        }
        
        // Find the leaf node and traverse recursively (same logic as ChatGptShareImporter)
        $currentNodeId = $conversationData['current_node'] ?? null;
        $orderedMessages = [];
        
        while ($currentNodeId && isset($mapping[$currentNodeId])) {
            $orderedMessages[] = $mapping[$currentNodeId];
            $currentNodeId = $mapping[$currentNodeId]['parent'] ?? null;
        }
        
        $orderedMessages = array_reverse($orderedMessages);

        foreach ($orderedMessages as $node) {
            $message = $node['message'] ?? null;
            if (!$message) continue;

            $role = $message['author']['role'] ?? 'unknown';
            $content = $message['content'] ?? [];
            
            $text = '';
            if (isset($content['parts']) && is_array($content['parts'])) {
                 $stringParts = array_map(function($part) {
                     return is_string($part) ? $part : (is_scalar($part) ? (string)$part : json_encode($part));
                 }, $content['parts']);
                 $text = implode("\n", array_filter($stringParts));
            } elseif (isset($content['text'])) {
                 $text = $content['text'];
            }
            
            if (!empty(trim($text))) {
               $authorHeading = ucfirst($role);
               $markdown .= "## {$authorHeading}\n\n{$text}\n\n";
            }
        }

        return $markdown;
    }
}
