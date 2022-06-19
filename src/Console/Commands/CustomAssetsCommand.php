<?php

namespace NormanHuth\NovaAssetsChanger\Console\Commands;

use NormanHuth\NovaAssetsChanger\Helpers\Process;

class CustomAssetsCommand extends Command
{
    protected string $novaPath = 'vendor/laravel/nova';
    protected Process $process;
    protected string $composerCommand = 'composer';
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

    protected function publishNovaAssets()
    {
        $this->info('Publish Nova assets');
        $this->call('vendor:publish', [
            '--tag'   => 'nova-assets',
            '--force' => true,
        ]);
    }

    protected function npmProduction()
    {
        $this->info('Run NPM production');
        $command = 'cd '.$this->novaPath.' && '.$this->npmCommand.' run production';
        $this->process->runCommand($command);
        foreach ($this->process->getOutput() as $output) {
            $this->line($output);
        }
    }

    protected function reinstallNova()
    {
        $this->info('Reinstall laravel/nova');
        $this->process->runCommand($this->composerCommand.' reinstall laravel/nova');
        foreach ($this->process->getOutput() as $output) {
            $this->line($output);
        }
    }

    protected function replaceComponents($path = 'Nova')
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

    protected function npmInstall()
    {
        $this->info('Run NPM install');
        $this->process->runCommand('cd '.$this->novaPath.' && '.$this->npmCommand.' i');
        foreach ($this->process->getOutput() as $output) {
            $this->line($output);
        }
    }

    protected function webpack()
    {
        if ($this->novaStorage->exists('webpack.mix.js.dist')) {
            $this->info('Create webpack.mix.js');
            $this->novaStorage->put('webpack.mix.js', $this->novaStorage->get('webpack.mix.js.dist'));
        }
    }
}
