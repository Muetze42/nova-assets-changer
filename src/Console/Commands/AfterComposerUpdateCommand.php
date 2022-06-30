<?php

namespace NormanHuth\NovaAssetsChanger\Console\Commands;

use Laravel\Nova\Nova;

class AfterComposerUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom-assets:after-composer-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run command only if a Nova update is detected (or the package has no version saved)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $currentNovaVersion = Nova::version();
        $changerVersion = null;
        if ($this->storage->exists($this->memoryFile)) {
            $content = json_decode($this->storage->get($this->memoryFile), true);
            if (!empty($content[$this->lastUseNovaVersionKey])) {
                $changerVersion = $content[$this->lastUseNovaVersionKey];
            }
        }

        if ($currentNovaVersion != $changerVersion) {
            $this->info('Run `nova:custom-assets` command');
            $this->call('nova:custom-assets');
            return 0;
        }

        $this->alert('No Nova Update detected. Skip running `nova:custom-assets` command');
        return 0;
    }
}
