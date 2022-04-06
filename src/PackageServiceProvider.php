<?php

namespace NormanHuth\NovaAssetsChanger;

use Illuminate\Support\ServiceProvider;
use NormanHuth\NovaAssetsChanger\Console\Commands\CustomAssetsCommand;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../resources' => resource_path('Nova'),
        ]);
        if ($this->app->runningInConsole()) {
            $this->commands([
                CustomAssetsCommand::class,
            ]);
        }
    }
}
