<?php

namespace NormanHuth\NovaAssetsChanger\Console\Commands;

use Illuminate\Console\Command;
use NormanHuth\NovaAssetsChanger\Helpers\Process;

/**
 * Todo: `nova:update` currently not exist in nova. Adjust this command if this changes
 */
class CustomAssetsCommand extends Command
{
    protected string $novaPath;
    protected string $novaResourcesPath;
    protected string $appResourcePath;
    protected Process $process;
    protected string $ds = DIRECTORY_SEPARATOR;
    protected string $novaVersion = 'u';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nova:custom-assets {--without-npm-install}';

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
        $npmInstall = !$this->option('without-npm-install');
        $this->novaPath = base_path('vendor'.$this->ds.'laravel'.$this->ds.'nova');
        $this->novaResourcesPath = $this->novaPath.$this->ds.'resources';
        $this->appResourcePath = resource_path('Nova'.$this->ds.'Nova');
        $this->process = new Process;

        $this->webpack();
        if ($npmInstall) {
            $this->installNPM();
        }
        $this->replaceComponents($this->appResourcePath);
        $this->productionRun();
        $this->info('Publish Nova assets');
        $this->call('vendor:publish', [
            '--tag'   => 'nova-assets',
            '--force' => true,
        ]);

        return 0;
    }

    protected function getNovaVersion()
    {
        $manifest = json_decode(file_get_contents(base_path('vendor/laravel/nova/composer.json')), true);
        $version = $manifest['version'] ?? '4.x';
        $this->novaVersion = $version;
    }

    protected function productionRun()
    {
        $this->info('Run NPM production');
        $command = 'cd '.$this->novaPath.' && npm run production';
        $this->process->runCommand($command);
        foreach ($this->process->getOutput() as $output) {
            $this->line($output);
        }
    }

    protected function replaceComponents(string $path)
    {
        $this->getNovaVersion();
        $files = glob($path.'/*');
        foreach ($files as $file) {
            if (in_array($file, ['..', '.'])) {
                continue;
            }
            if (str_ends_with($file, '.vue')) {
                $target = $this->novaResourcesPath.str_replace($this->appResourcePath, '', $file);
                $this->line('Replace file: '.$file);
                $this->replaceComponent($file, $target);
            } elseif (is_dir($file)) {
                $this->replaceComponents($file);
            }
        }
    }

    protected function replaceComponent(string $file, string $target)
    {
        $fileInfo = pathinfo($file);

        $backupFile = rtrim($fileInfo['dirname'], '/\\').'/'.
            str_replace($fileInfo['filename'], $fileInfo['filename'].'-nova-'.$this->novaVersion.'.'.$fileInfo['extension'], $fileInfo['filename']);

        $content = file_get_contents($file);

        if (!file_exists($backupFile)) {
            file_put_contents($backupFile, $content);
        }

        file_put_contents($target, $content);
    }

    protected function strReplaceLast(string $search, string $replace, string $string): string
    {
        if (($pos = strrpos($string, $search)) !== false) {
            $searchLength = strlen($search);
            $string = substr_replace($string, $replace, $pos, $searchLength);
        }
        return $string;
    }

    /**
     * Install NPM packages
     */
    protected function installNPM()
    {
        $this->info('Run NPM install');
        $command = 'cd '.$this->novaPath.' && npm i';
        $this->process->runCommand($command);
        foreach ($this->process->getOutput() as $output) {
            $this->line($output);
        }
    }

    /**
     * Create or overwrite Nova webpack.mix.js
     */
    protected function webpack()
    {
        $content = file_get_contents($this->novaPath.'/webpack.mix.js.dist');
        file_put_contents($this->novaPath.'/webpack.mix.js', $content);
    }
}
