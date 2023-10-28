<?php

namespace NormanHuth\NovaAssetsChanger\Console\Commands;

use JsonException;
use Laravel\Nova\Nova;
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
     * @return bool
     */
    protected function disableNotifications(): bool
    {
        return config('this.nova.asset-changer.disable-notifications', false);
    }

    /**
     * Execute the console command.
     *
     * @throws JsonException
     * @return int
     */
    public function handle(): int
    {
        $this->process = new Process;
        $this->novaPath = base_path($this->novaPath);

        $this->reinstallNova();
        $this->webpack();
        $this->npmInstall();
        $this->postCssConfig();
        $this->replaceComponents();
        $this->registerPages();
        $this->addCustomCSS();
        $this->npmProduction();
        $this->publishNovaAssets();
        $this->saveCurrentNovaVersion();

        return 0;
    }

    /**
     * @return void
     */
    protected function addCustomCSS(): void
    {
        $file = 'custom.css';
        if ($this->storage->exists($file)) {
            $this->info(__('Register Nova custom CSS'));
            $content = $this->storage->get($file);
            $this->novaStorage->put('resources/css/' . $file, $content);
            $cssContent = $this->novaStorage->get('resources/css/app.css');
            $cssContent = str_replace("@import 'nova';", "@import 'nova';\n@import '" . $file . "';", $cssContent);
            $this->novaStorage->put('resources/css/app.css', $cssContent);
        }
    }

    /**
     * @return void
     */
    protected function registerPages(): void
    {
        $files = $this->storage->files('New/pages');
        foreach ($files as $file) {
            $info = pathinfo($file);
            $basename = basename($file);
            if ($this->novaStorage->exists('resources/js/pages/' . $basename)) {
                $this->error(__('Skip `:file`. File already exist in Nova', ['file' => $file]));
                continue;
            }
            if ($info['extension'] == 'vue') {
                $this->info('Register ' . $file);
                $content = $this->storage->get($file);
                $this->novaStorage->put('resources/js/pages/' . $basename, $content);

                $content = $this->novaStorage->get('resources/js/app.js');
                if (!str_contains($content, 'Nova.' . $basename)) {
                    $content = str_replace("'Nova.Login': require('@/pages/Login').default,",
                        "'Nova.Login': require('@/pages/Login').default,\n      'Nova." . $info['filename'] . "': require('@/pages/" . $basename . "').default,",
                        $content);

                    $this->novaStorage->put('resources/js/app.js', $content);
                }
            }
        }
    }

    /**
     * @throws JsonException
     * @return void
     */
    protected function saveCurrentNovaVersion(): void
    {
        $this->storage->put($this->memoryFile, json_encode([$this->lastUseNovaVersionKey => Nova::version()], JSON_THROW_ON_ERROR));
    }

    /**
     * @return void
     */
    protected function publishNovaAssets(): void
    {
        $this->info('Publish Nova assets');
        usleep(250000);
        $this->call('vendor:publish', [
            '--tag' => 'nova-assets',
            '--force' => true,
        ]);
    }

    /**
     * @return void
     */
    protected function npmProduction(): void
    {
        $this->info('Run NPM production');
        $novaPath = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->novaPath), '/\\') . DIRECTORY_SEPARATOR;
        $command = 'cd ' . $novaPath . ' && ' . $this->npmCommand . ' run production';
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
        $success = false;
        $this->process->runCommand($this->composerCommand . ' reinstall laravel/nova');
        foreach ($this->process->getOutput() as $output) {
            if (str_contains($output, $this->installStrContainsCheck1) && str_contains($output, $this->installStrContainsCheck2)) {
                $success = true;
            }
            $this->line($output);
        }
        if (!$success) {
            $this->error('It could’t detect a new installation of Nova.');
            die();
        }
    }

    /**
     * @param string $path
     *
     * @return void
     */
    protected function replaceComponents(string $path = 'Nova'): void
    {
        $files = $this->storage->files($path);
        foreach ($files as $file) {
            $base = explode('/', $file, 2)[1];
            $this->info('Processing ' . $base);
            if ($this->novaStorage->missing('resources/' . $base)) {
                $this->error('Skip file. `' . $base . '` not found in the Nova installation');
                continue;
            }
            $customContent = $this->storage->get($file);
            $novaContent = $this->novaStorage->get('resources/' . $base);
            if ($this->storage->missing('Backup/' . $base)) {
                $this->storage->put('Backup/' . $base, $novaContent);
            } else {
                $backupContent = $this->storage->get('Backup/' . $base);
                if (trim($backupContent) != trim($novaContent)) {
                    if (!$this->confirm('The `' . $base . '` file seems to have changed. Do you wish to continue and renew the backup file?')) {
                        $this->error('Abort');
                        die();
                    }
                    $this->storage->put('Backup/' . $base, $novaContent);
                }

                $this->novaStorage->put('resources/' . $base, $customContent);
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
        $this->process->runCommand('cd ' . $this->novaPath . ' && ' . $this->npmCommand . ' ci');
        foreach ($this->process->getOutput() as $output) {
            $this->line($output);
        }
    }

    protected function postCssConfig(): void
    {
        if (!$this->novaStorage->exists('postcss.config.js')) {
            $this->info('postcss.config.js');
            $contents = file_get_contents(
                dirname(__DIR__, 3) . '/stubs/postcss.config.js.stub'
            );

            $this->novaStorage->put('postcss.config.js', $contents);
        }
    }

    /**
     * @return void
     */
    protected function webpack(): void
    {
        if ($this->novaStorage->exists('webpack.mix.js.dist')) {
            $this->info('Create webpack.mix.js');
            $content = $this->novaStorage->get('webpack.mix.js.dist');
            if ($this->disableNotifications() && !str_contains($content, '.disableNotifications()')) {
                $content = str_replace('.version()', '.version().disableNotifications()', $content);
            }
            $this->novaStorage->put('webpack.mix.js', $content);
        }
    }
}
