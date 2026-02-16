<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            [
                'name' => 'Map Generator',
                'description' => 'Generate map-related artifacts and assets.',
            ],
            [
                'name' => 'Context Controlled Development Factory to Auto Pipeline',
                'description' => 'Coordinate automated story-to-task workflow execution.',
            ],
            [
                'name' => 'Todo List',
                'description' => 'Sprint-ready task tracking app with CRUD and completion flow.',
            ],
        ])->each(function (array $project): void {
            Project::query()->updateOrCreate(
                ['name' => $project['name']],
                ['description' => $project['description']]
            );
        });
    }
}
