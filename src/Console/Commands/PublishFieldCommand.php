<?php

namespace NormanHuth\NovaAssetsChanger\Console\Commands;

class PublishFieldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom-assets:publish:field';

    /**
     * Field Vue Locations
     *
     * @var array|string[]
     */
    protected array $locations = [
        '/js/fields/Detail/',
        '/js/fields/Form/',
        '/js/fields/Index/',
    ];

    /**
     * Available fields
     *
     * @var array
     */
    protected array $fields = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish Nova field assets for the asset changer';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->choose();

        return 0;
    }

    /**
     * Choose a field
     *
     * @return void
     */
    protected function choose(): void
    {
        $fields = $this->novaStorage->files('src/Fields');

        $i = 0;
        $array = [];
        foreach ($fields as $field) {
            $info = pathinfo($field);
            $filename = $info['filename'];

            if ($info['extension'] != 'php' && $filename != 'Field') {
                continue;
            }

            $hasFile = false;
            foreach ($this->locations as $location) {
                if ($this->novaStorage->exists('resources'.$location.$filename.'Field.vue')) {
                    $hasFile = true;
                }
            }

            if (!$hasFile) {
                continue;
            }

            $array[$i] = $filename;
            $this->line('['.$i++.'] '.$filename);
        }

        $select = $this->ask('Choose a field to publish VUE assets');

        if (empty($array[$select])) {
            $this->choose();
            return;
        }

        $this->publish($array[$select]);
    }

    /**
     * Publish Field Assets
     *
     * @param string $field
     * @return void
     */
    protected function publish(string $field): void
    {
        foreach ($this->locations as $location) {
            $file = $location.$field.'Field.vue';
            if ($this->novaStorage->missing('resources'.$file)) {
                continue;
            }
            if ($this->storage->exists('Nova'.$file)) {
                $this->error('File already exists. Skip '.$file);
                continue;
            }
            $this->storage->put('Nova'.$file, $this->novaStorage->get('resources'.$file));
            $this->line('<info>Copied File</info> <comment>['.$this->novaStorage->path('resources'.$file).']</comment> <info>To</info> <comment>['.$this->storage->path('Nova'.$file).']</comment>');
        }
    }
}
