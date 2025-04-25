<?php

namespace Glama\Contracts;

interface LLMProviderContract
{
    public function needsServer(): bool;
    public function openServer(): static;
    public function prompt(string $prompt = '', array $options = []): static;
    public function get(): array|string;
}
