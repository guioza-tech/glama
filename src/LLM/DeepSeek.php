<?php
declare(strict_types=1);

namespace Glama\LLM;

use Glama\Exceptions\DeepSeekException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;

final class DeepSeek
{
    private const REQUIRED = ['host', 'key'];
    private const DEFAULTS = [
        'timeout'        => 30,
        'max_retries'    => 3,
        'endpoint'       => '/v1/chat/completions',
        'default_model'  => 'deepseek-reasoner',
        'temperature'    => 0.7,
        'max_tokens'     => 4_096,
        'system_message' => 'You are a JSON response generator. Always return valid JSON in the form:
{
  "result": {
    "content": "...",
    "metadata": { ... }
  }
}',
    ];

    private PendingRequest $http;
    private array $cfg;

    public function __construct(?array $config = null)
    {
        $this->cfg  = $this->mergeConfig($config);
        $this->http = $this->buildClient();
    }

    public function json(string $prompt, ?string $model = null, array $options = []): array
    {
        $payload  = $this->payload($prompt, $model, $options);
        $response = $this->http->post($this->cfg['endpoint'], $payload);

        return $this->parse($response);
    }

    private function mergeConfig(?array $override): array
    {
        $file = config('glama.providers.deepseek') ?? [];
        $cfg  = array_replace(self::DEFAULTS, $file, $override ?? []);

        // Validate required parameters
        $missing = array_diff(self::REQUIRED, array_keys($cfg));
        if ($missing) {
            throw new InvalidArgumentException(
                'DeepSeek: Missing required configuration keys: ' . implode(', ', $missing)
            );
        }

        // Normalize and validate host
        $cfg['host'] = rtrim($cfg['host'], '/');
        if (!Str::startsWith($cfg['host'], ['http://', 'https://'])) {
            throw new InvalidArgumentException('DeepSeek: Host must start with http:// or https://');
        }

        return $cfg;
    }

    private function buildClient(): PendingRequest
    {
        return Http::baseUrl($this->cfg['host'])
            ->timeout($this->cfg['timeout'])
            ->retry(
                $this->cfg['max_retries'],
                500,
                fn ($e) => $e->getCode() >= 500
            )
            ->withHeaders(
                [
                'Authorization' => 'Bearer ' . $this->cfg['key'],
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                ]
            );
    }

    private function payload(string $prompt, ?string $model, array $options): array
    {
        // Prevent overriding system and user messages
        $options = Arr::except($options, ['messages']);

        return array_replace_recursive(
            [
            'model'           => $model ?? $this->cfg['default_model'],
            'messages'        => [
                ['role' => 'system', 'content' => $this->cfg['system_message']],
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => $this->cfg['temperature'],
            'max_tokens'      => $this->cfg['max_tokens'],
            ], $options
        );
    }

    private function parse(Response $response): array
    {
        if ($response->failed()) {
            throw new DeepSeekException(
                "DeepSeek API error ({$response->status()}): " . $response->body(),
                $response->status()
            );
        }

        $raw = Arr::get($response->json(), 'choices.0.message.content');

        if (!$raw || !is_string($raw)) {
            throw new DeepSeekException('DeepSeek: Unexpected response structure - missing content');
        }

        try {
            return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new DeepSeekException(
                'DeepSeek: Failed to decode JSON response: ' . $e->getMessage(),
                0,
            );
        }
    }
}
