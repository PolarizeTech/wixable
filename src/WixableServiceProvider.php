<?php

namespace Wixable;

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;
use Wixable\Console\Commands\ImportCommand;
use Wixable\Support\Wixable as SupportWixable;

class WixableServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('wixable', fn () => new SupportWixable);

    }

    public function boot()
    {
        Schedule::command('wixable:import')->everyFiveMinutes();

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'wixable.migrations');

        $this->mergeConfigFrom(
            __DIR__.'/../config/services.php', 'services'
        );

        $this->commands([
            ImportCommand::class,
        ]);
    }
}
