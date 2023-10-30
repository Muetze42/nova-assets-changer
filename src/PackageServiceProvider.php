<?php

namespace NormanHuth\NovaAssetsChanger;

use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        /* @deprecated */
        $this->publishes([
            __DIR__.'/../resources' => resource_path('Nova'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands($this->getCommands());
        }
    }

    /**
     * Get all package commands
     *
     * @return array
     */
    protected function getCommands(): array
    {
        return array_filter(array_map(function ($item) {
            return '\\'.__NAMESPACE__.'\\Console\\Commands\\'.pathinfo($item, PATHINFO_FILENAME);
        }, glob(__DIR__.'/Console/Commands/*.php')), function ($item) {
            return class_basename($item) != 'Command';
        });
    }
}
