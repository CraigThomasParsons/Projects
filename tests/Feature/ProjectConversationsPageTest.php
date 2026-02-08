<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Ensures the project conversations page renders without Blade syntax errors.
 */
class ProjectConversationsPageTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function it_renders_conversations_page_for_a_project(): void
    {
        // Avoid Vite manifest lookups during tests.
        $this->withoutVite();

        // Arrange a project so the route parameter resolves.
        $project = Project::create([
            'name' => 'Sample Project',
            'description' => 'Demo description',
        ]);

        // Act by requesting the conversations page.
        $response = $this->get(route('projects.show', $project));

        // Assert the view renders successfully and shows expected text.
        $response->assertOk();
        $response->assertSee('Back to Projects');
        $response->assertSee('No conversations yet');
    }
}
