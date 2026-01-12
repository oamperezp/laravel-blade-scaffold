<?php

namespace AmpTech\LaravelBladeScaffold;

use Illuminate\Support\ServiceProvider;
use AmpTech\LaravelBladeScaffold\Commands\GenerateFormCommand;

class BladeScaffoldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateFormCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/templates' => resource_path('views/vendor/blade-scaffold/templates'),
            ], 'blade-scaffold-templates');

            $this->publishes([
                __DIR__.'/components' => resource_path('views/components'),
            ], 'blade-scaffold-components');

            $this->publishes([
                __DIR__.'/templates' => resource_path('views/vendor/blade-scaffold/templates'),
                __DIR__.'/components' => resource_path('views/components'),
            ], 'blade-scaffold');
        }

        $this->loadViewsFrom(__DIR__.'/components', 'blade-scaffold');
    }
}