<?php
namespace Glama\LLM;

use Glama\Contracts\LLMProviderContract;
use Glama\Process;

class Ollama implements LLMProviderContract
{
    private string $prompt = '';
    private array $options = [];

    public function __construct(private readonly string $model = 'deepseek-r1:8b')
    {
    }

    /**
     * @return
     */
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
     * Execute the prompt against Ollama using the command line and get the response
     *
     * @return array|string
     */
    public function get(): array|string
    {
        $this->openServer();

        $escapedPrompt = escapeshellarg($this->prompt);

        $commandStr = "ollama run {$this->model} {$escapedPrompt}";

        if (!empty($this->options)) {
            if (isset($this->options['temperature'])) {
                $commandStr .= " --temperature " . floatval($this->options['temperature']);
            }
        }

        try {
            $process = Process::run(["sh", "-c", $commandStr]);
            $output = trim($process->cleanerLLM());

            if (!empty($this->options['full_response'])) {
                return [
                    'model' => $this->model,
                    'prompt' => $this->prompt,
                    'response' => $output,
                    'raw' => $process->raw()
                ];
            }

            return $output;
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to execute Ollama command: " . $e->getMessage());
        }
    }
}

