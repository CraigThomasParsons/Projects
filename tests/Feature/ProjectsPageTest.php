<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\ProjectsPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Ensure the projects page renders the Foundation modal markup.
 */
class ProjectsPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Configure the test database connection.
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('view.compiled', sys_get_temp_dir() . '/laravel-views');
    }

    #[Test]
    public function it_renders_the_project_modal_markup(): void
    {
        // Avoid Vite manifest lookups during tests.
        $this->withoutVite();

        // Ensure Blade can write compiled views during tests.
        $compiledViewPath = config('view.compiled');
        if (!is_dir($compiledViewPath)) {
            mkdir($compiledViewPath, 0775, true);
        }

        // Render the component and check for standard modal markup
        Livewire::test(ProjectsPage::class)
            ->assertSee('Add Project')
            ->assertSeeHtml('data-reveal')
            ->assertSeeHtml('id="add-project-modal"')
            ->assertSeeHtml('wire:ignore.self')
            ->assertSee('close-button');
    }
}
