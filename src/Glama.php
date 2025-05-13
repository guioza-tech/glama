<?php

namespace Glama;

use Glama\Contracts\LLMProviderContract;
use Glama\LLM\Ollama;
use Glama\Providers\LLMProvider;

class Glama
{
    /**
     * TODO: Improve the extensibility
     *
     * @return LLMProvider
     */
    public function provider(): LLMProviderContract
    {
        return new Ollama;
    }
}
