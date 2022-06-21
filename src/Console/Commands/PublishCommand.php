<?php

namespace NormanHuth\NovaAssetsChanger\Console\Commands;

class PublishCommand extends Command
{
    /**
     * @var array|string[]
     */
    protected array $baseDirectories = [
        'resources/css',
        'resources/js',
        'resources/views',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom-assets:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish Nova asset for the asset changer';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->selectBaseDirectory();

        return 0;
    }

    /**
     * @return void
     */
    protected function selectBaseDirectory(): void
    {
        $i = 0;
        foreach ($this->baseDirectories as $baseDirectory) {
            $this->line('['.$i++.'] '.$baseDirectory);
        }

        $directory = $this->ask('Choose a directory');

        if (empty($this->baseDirectories[$directory])) {
            $this->break();
            $this->selectBaseDirectory();
        }
        $this->handleDirectory($this->baseDirectories[$directory]);
    }

    /**
     * @param $path
     * @return void
     */
    protected function handleDirectory($path): void
    {
        $this->error($path);
        $this->break();
        $directories = $this->novaStorage->directories($path);
        $i = 0;
        $array = [];
        if (count($directories)) {
            $this->info('Directories: ');
            foreach ($directories as $directory) {
                $array[$i] = ['directory' => $directory];
                $this->line('['.$i++.'] '.$directory);
            }
        }
        $files = $this->novaStorage->files($path);
        if (count($files)) {
            $this->info('Files: ');
            foreach ($files as $file) {
                $array[$i] = $file;
                $this->line('['.$i++.'] '.$file);
            }
        }

        $select = $this->ask('Choose...');

        if (empty($array[$select])) {
            $this->handleDirectory($path);
            return;
        }

        if (!empty($array[$select]['directory'])) {
            $this->handleDirectory($array[$select]['directory']);
            return;
        }

        $file = 'Nova/'.explode('/', $array[$select], 2)[1];
        if ($this->storage->exists($file) && !$this->confirm('The file `'.$file.'` already exist. Overwrite this file?')) {
            die('Abort');
        }

        $this->storage->put($file, $this->novaStorage->get($array[$select]));
        $this->line('<info>Copied File</info> <comment>['.$this->novaStorage->path($array[$select]).']</comment> <info>To</info> <comment>['.$this->storage->path($file).']</comment>');
    }
}
