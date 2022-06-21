<?php

namespace NormanHuth\NovaAssetsChanger\Console\Commands;

use NormanHuth\NovaAssetsChanger\Helpers\Process;

class CustomAssetsCommand extends Command
{
    /**
     * CLI Composer Command
     *
     * @var string
     */
    protected string $composerCommand = 'composer';

    /**
     * CLI NPM Command
     *
     * @var string
     */
    protected string $npmCommand = 'npm';

    /**
     * `str_contains` Check 1 for Nova install
     *
     * @var string
     */
    protected string $installStrContainsCheck1 = 'Installing laravel/nova';

    /**
     * `str_contains` Check 2 for Nova install
     *
     * @var string
     */
    protected string $installStrContainsCheck2 = 'Installing laravel/nova';

    /**
     * Nova Path
     *
     * @var string
     */
    protected string $novaPath = 'vendor/laravel/nova';

    /**
     * Process output class
     *
     * @var Process
     */
    protected Process $process;

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
        $succes = false;
        $this->process->runCommand($this->composerCommand.' reinstall laravel/nova');
        foreach ($this->process->getOutput() as $output) {
            if (str_contains($output, $this->installStrContainsCheck1) && str_contains($output, $this->installStrContainsCheck2)) {
                $succes = true;
            }
            $this->line($output);
        }
        if (!$succes) {
            $this->error('It could’t detect a new installation of Nova.');
            die();
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
                        $this->error('Abort');
                        die();
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
