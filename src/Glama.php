<?php

namespace Glama;

use Glama\Contracts\LLMProviderContract;
use Glama\LLM\DeepSeek;
use Glama\LLM\Ollama;
use Glama\Providers\LLMProvider;

class Glama
{
    /**
     * TODO: Improve the extensibility
     *
     * @return LLMProvider
     */
    public function provider(): LLMProviderContract|DeepSeek
    {
        if(config('glama.default_provider') === 'deepseek') {
            return new DeepSeek();
        }
        return new Ollama;
    }
}
