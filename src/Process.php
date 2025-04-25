<?php

namespace Glama;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process as SymfonyProcess;


/**
 * Process
 */
final class Process
{

    /**
     * @param SymfonyProcess $process
     */
    public function __construct(private readonly SymfonyProcess $process)
    {
    }
    /**
     * @param array<string> $commands
     */
    public static function run(array $commands)
    {
        $process = new SymfonyProcess($commands);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return new Process($process);
    }

    /**
     * @return string
     */
    public function raw():string
    {
        return $this->process->getOutput();
    }

    /**
     * @return string
     */
    public function cleanerLLM():string
    {
        $out = $this->process->getOutput();
        return str_replace(["<think>", "</think>"], "", trim($out));
    }

    public static function isRunningOnBG(string $processName)
    {
        $runners =  Process::run(["ps", "aux"])->raw();
        return strpos($runners, $processName);
    }

}
