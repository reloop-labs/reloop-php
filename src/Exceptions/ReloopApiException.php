<?php

namespace Reloop\Exceptions;

class ReloopApiException extends \RuntimeException
{
    /**
     * @param array{message?: string, why?: string, tip?: string, link?: string} $body
     */
    public function __construct(
        public readonly int $status,
        public readonly string $statusText,
        public readonly array $body = [],
        ?\Throwable $previous = null,
    ) {
        $message = $body['message'] ?? '';
        if ($message === '') {
            $message = $status === 0
                ? 'Reloop network error: ' . $statusText
                : "Reloop API Error: {$status} {$statusText}";
        }
        parent::__construct($message, $status, $previous);
    }
}
