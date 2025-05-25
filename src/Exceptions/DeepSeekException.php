<?php

namespace Glama\Exceptions;

class DeepSeekException extends \RuntimeException
{
    public function __construct(
        string $message,
        int $code = 0,
        public readonly array $context = []
    ) {
        parent::__construct($message, $code);
    }
}
