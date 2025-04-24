<?php

namespace Glama;

class Glama
{
    private string $model = 'deepseek-r1';
    private array $response = [];
    private bool $isRunning = false;

    public static function init(): static
    {
        $instance = new static();

        // Check if ollama is already running
        exec("pgrep -f 'ollama serve'", $output, $returnCode);

        if ($returnCode !== 0) {
            // Start ollama in the background
            exec("ollama serve > /dev/null 2>&1 &", $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \RuntimeException("Failed to start ollama service");
            }

            // Wait a moment for the service to start
            sleep(2);
        }

        $instance->isRunning = true;
        return $instance;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }
    public function do(string $prompt): static
    {
        $safeModel = escapeshellarg($this->model);
        $safePrompt = escapeshellarg($prompt);

        $command = "ollama run $safeModel $safePrompt";
        exec($command, $output, $returnCode);

        // Store the response
        $this->response = $output;

        return $this;
    }

    public function get(): mixed
    {
        return $this->response;
    }
}
