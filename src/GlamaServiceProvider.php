<?php

namespace Glama;

use Glama\Commands\GlamaWatcher;
use Illuminate\Support\ServiceProvider;

class GlamaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                GlamaWatcher::class,
                ]
            );
        }

        $this->publishes(
            [
            __DIR__.'/config/glama.php' => config_path('glama.php'),
            ], 'config'
        );

        $this->mergeConfigFrom(
            __DIR__.'/config/glama.php', 'glama'
        );
    }
}
