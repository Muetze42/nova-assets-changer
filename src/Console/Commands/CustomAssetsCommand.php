<?php

namespace NormanHuth\NovaAssetsChanger\Console\Commands;

use NormanHuth\NovaAssetsChanger\Helpers\Process;

class CustomAssetsCommand extends Command
{
    /**
     * @var string
     */
    protected string $novaPath = 'vendor/laravel/nova';
    /**
     * @var Process
     */
    protected Process $process;
    /**
     * @var string
     */
    protected string $composerCommand = 'composer';
    /**
     * @var string
     */
    protected string $npmCommand = 'npm';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nova:custom-assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make changes to Nova assets that are not dynamic.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->process = new Process;
        $this->novaPath = base_path($this->novaPath);

        $this->reinstallNova();
        $this->replaceComponents();
        $this->webpack();
        $this->npmInstall();
        $this->npmProduction();
        $this->publishNovaAssets();

        return 0;
    }

    /**
     * @return void
     */
    protected function publishNovaAssets(): void
    {
        $this->info('Publish Nova assets');
        $this->call('vendor:publish', [
            '--tag'   => 'nova-assets',
            '--force' => true,
        ]);
    }

    /**
     * @return void
     */
    protected function npmProduction(): void
    {
        $this->info('Run NPM production');
        $command = 'cd '.$this->novaPath.' && '.$this->npmCommand.' run production';
        $this->process->runCommand($command);
        foreach ($this->process->getOutput() as $output) {
            $this->line($output);
        }
    }

    /**
     * @return void
     */
    protected function reinstallNova(): void
    {
        $this->info('Reinstall laravel/nova');
        $this->process->runCommand($this->composerCommand.' reinstall laravel/nova');
        foreach ($this->process->getOutput() as $output) {
            $this->line($output);
        }
    }

    /**
     * @param string $path
     * @return void
     */
    protected function replaceComponents(string $path = 'Nova'): void
    {
        $files = $this->storage->files($path);
        foreach ($files as $file) {
            $base = explode('/', $file, 2)[1];
            $this->info('Processing '.$base);
            if ($this->novaStorage->missing('resources/'.$base)) {
                $this->error('Skip file. `'.$base.'` not found in the Nova installation');
                continue;
            }
            $customContent = $this->storage->get($file);
            $novaContent = $this->novaStorage->get('resources/'.$base);
            if ($this->storage->missing('Backup/'.$base)) {
                $this->storage->put('Backup/'.$base, $novaContent);
            } else {
                $backupContent = $this->storage->get('Backup/'.$base);
                if (trim($backupContent) != trim($novaContent)) {
                    if (!$this->confirm('The `'.$base.'` file seems to have changed. Do you wish to continue and renew the backup file?')) {
                        die('Abort');
                    } else {
                        $this->storage->put('Backup/'.$base, $novaContent);
                    }
                }

                $this->novaStorage->put('resources/'.$base, $customContent);
            }
        }
        $directories = $this->storage->directories($path);
        foreach ($directories as $directory) {
            $this->replaceComponents($directory);
        }
    }

    /**
     * @return void
     */
    protected function npmInstall(): void
    {
        $this->info('Run NPM install');
        $this->process->runCommand('cd '.$this->novaPath.' && '.$this->npmCommand.' i');
        foreach ($this->process->getOutput() as $output) {
            $this->line($output);
        }
    }

    /**
     * @return void
     */
    protected function webpack(): void
    {
        if ($this->novaStorage->exists('webpack.mix.js.dist')) {
            $this->info('Create webpack.mix.js');
            $this->novaStorage->put('webpack.mix.js', $this->novaStorage->get('webpack.mix.js.dist'));
        }
    }
}
