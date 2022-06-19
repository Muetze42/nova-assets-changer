<?php

namespace NormanHuth\NovaAssetsChanger\Helpers;

use Illuminate\Support\Facades\Log;

class Process
{
    protected bool $commandLogging = false;
    protected bool $outputLogging = false;
    protected ?array $output;
    protected ?array $errorOutput;

    /**
     * @param string $command
     * @return bool
     */
    public function runCommand(string $command): bool
    {
        $this->output = $this->errorOutput = null;

        $this->commandLogging($command);

        exec($command.' 2>&1', $output, $resultCode);

        if ($resultCode) {
            report(implode("\n", $output));
            $this->output = $output;

            return false;
        }

        $this->outputLogging($output);
        $this->output = $output;

        return true;
    }

    /**
     * @param array $commands
     * @return bool
     */
    public function runCommands(array $commands): bool
    {
        foreach ($commands as $command) {
            if (!$this->runCommand($command)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the output
     *
     * @return array
     */
    public function getOutput(): array
    {
        return (array) $this->output;
    }

    /**
     * Return the error output
     *
     * @return array|null
     */
    public function getError(): ?array
    {
        return $this->errorOutput;
    }

    /**
     * Logging output if enable
     *
     * @param object|array|string $output
     */
    protected function outputLogging(object|array|string $output): void
    {
        if ($this->outputLogging) {
            $this->infoLog($output);
        }
    }

    /**
     * Logging commands if enable
     *
     * @param object|array|string $command
     */
    protected function commandLogging(object|array|string $command): void
    {
        if ($this->commandLogging) {
            $this->infoLog('Run command `'.$command.'`:');
        }
    }

    /**
     * @param object|array|string $content
     */
    protected function infoLog(object|array|string $content): void
    {
        if (is_array($content)) {
            $content = implode("\n", $content);
        }

        if (is_object($content)) {
            $content = print_r($content, true);
        }

        Log::info($content);
    }
}
