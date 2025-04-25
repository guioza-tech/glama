<?php

namespace Glama\LLM;

use Glama\Contracts\LLMProviderContract;
use Glama\Process;
use Illuminate\Support\Facades\Http;

class Ollama implements LLMProviderContract
{
    private string $prompt = '';
    private array $options = [];

    public function __construct(private readonly string $model = 'llama3.2')
    {
    }

    public function needsServer(): bool
    {
        return true;
    }

    /**
     * @return static
     */
    public function openServer(): static
    {
        if (!Process::isRunningOnBG("ollama serve")) {
            Process::run(["sh", "-c", "ollama serve > /dev/null 2>&1 &"]);
            sleep(2);
        }

        return $this;
    }

    /**
     * @param  string $prompt
     * @param  array  $options
     * @return static
     */
    public function prompt(string $prompt = '', array $options = []): static
    {
        $this->prompt = $prompt;
        $this->options = $options;
        return $this;
    }

    /**
     * Set the model to use
     *
     * @param  string $model
     * @return static
     */
    public function setModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Execute the prompt against Ollama using the HTTP API and get the response
     *
     * @return array|string
     */
    public function get(): array|string
    {
        $this->openServer();

        try {
            $result = Http::timeout(100000)->post(
                'localhost:11434/api/generate',
                [
                'model' => $this->model,
                'prompt' => $this->prompt,
                'stream' => false,
                'temperature' => isset($this->options['temperature']) ? floatval($this->options['temperature']) : null,
                ]
            )->json();

            $response = $result['response'] ?? '';
            return str_replace(["<think>", "</think>", PHP_EOL], "", trim($response));
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

}
