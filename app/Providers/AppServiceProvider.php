<?php

namespace App\Providers;

use App\Models\Project;
use App\Observers\ProjectObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Emit projection webhooks when canonical project records change.
        Project::observe(ProjectObserver::class);

        if (request()->getHost() === 'projects.elasticgun.com') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
