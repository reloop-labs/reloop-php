<?php

namespace Reloop\Exceptions;

class ReloopValidationException extends \InvalidArgumentException
{
    public function __construct(
        string $message,
        public readonly ?string $field = null,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
