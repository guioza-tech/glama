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
    public function raw(): string
    {
        return $this->process->getOutput();
    }

    /**
     * @return string
     */
    public function cleanerLLM(): string
    {
        $out = $this->process->getOutput();
        return str_replace(["<think>", "</think>"], "", trim($out));
    }

    public static function isRunningOnBG(string $processName)
    {
        $runners =  Process::run(["/usr/bin/ps", "aux"])->raw();
        return strpos($runners, $processName);
    }

    /**
     * Get serialized array of running Ollama processes
     *
     * @return array<int, array<string, string|int>> Array of process information
     */
    public static function getOllamaProcesses(): array
    {
        $output = self::run(["ollama", "ps"])->raw();

        $lines = array_filter(explode("\n", trim($output)));

        $headers = preg_split('/\s+/', trim($lines[0]));

        $result = [];

        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);

            preg_match('/(\S+)\s+(\S+)\s+(\S+\s\S+)\s+(\d+%\s\S+)\s+(.+)/', $line, $matches);

            if (count($matches) >= 6) {
                $item = [
                $headers[0] => $matches[1],        // NAME
                $headers[1] => $matches[2],        // ID
                $headers[2] => $matches[3],        // SIZE
                $headers[3] => $matches[4],        // PROCESSOR
                $headers[4] => $matches[5],        // UNTIL
                ];

                $result[] = $item;
            }
        }

        return $result;

    }
}
