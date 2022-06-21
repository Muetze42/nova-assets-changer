<?php

namespace NormanHuth\NovaAssetsChanger\Console\Commands;

use Illuminate\Console\Command as BaseCommand;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class Command extends BaseCommand
{
    /**
     * Resource Storage
     *
     * @var Filesystem
     */
    protected Filesystem $storage;

    /**
     * Nova Storage
     *
     * @var Filesystem
     */
    protected Filesystem $novaStorage;

    /**
     * Nova Vendor Path
     *
     * @var string
     */
    protected string $novaVendorPath = 'vendor/laravel/nova';

    /**
     * Resource Path
     *
     * @var string
     */
    protected string $resourcePath = 'Nova';

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
            'root'   => resource_path($this->resourcePath),
            'throw'  => false,
        ]);
        $this->novaStorage = Storage::build([
            'driver' => 'local',
            'root'   => base_path($this->novaVendorPath),
            'throw'  => false,
        ]);

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function break(): void
    {
        $this->newLine(3);
        $this->comment('--------------------');
    }
}
