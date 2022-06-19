<?php

namespace NormanHuth\NovaAssetsChanger\Console\Commands;

use Illuminate\Console\Command as BaseCommand;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class Command extends BaseCommand
{
    protected Filesystem $storage;
    protected Filesystem $novaStorage;

    /**
     * Create a new console command instance.
     * Create 2 new On-Demand Disks
     *
     * @return void
     */
    public function __construct()
    {
        $this->storage = Storage::build([
            'driver' => 'local',
            'root'   => resource_path('Nova'),
            'throw'  => false,
        ]);
        $this->novaStorage = Storage::build([
            'driver' => 'local',
            'root'   => base_path('vendor/laravel/nova'),
            'throw'  => false,
        ]);

        parent::__construct();
    }
}
