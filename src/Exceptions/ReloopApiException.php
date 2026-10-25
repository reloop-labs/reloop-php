<?php

namespace Reloop\Exceptions;

class ReloopApiException extends \RuntimeException
{
    /**
     * Decoded error payload: message, why, fix, link. `tip` is a deprecated
     * mirror of `fix`, kept for callers of 2.0.0.
     *
     * @var array{message?: string, why?: string, fix?: string, link?: string, tip?: string}
     */
    public readonly array $body;

    /**
     * @param array{message?: string, why?: string, fix?: string, link?: string, tip?: string} $body
     */
    public function __construct(
        public readonly int $status,
        public readonly string $statusText,
        array $body = [],
        ?\Throwable $previous = null,
    ) {
        $fix = $body['fix'] ?? $body['tip'] ?? null;
        if (is_string($fix)) {
            $body['fix'] = $fix;
            $body['tip'] = $fix;
        }
        $this->body = $body;

        $message = $body['message'] ?? '';
        if ($message === '') {
            $message = $status === 0
                ? 'Reloop network error: ' . $statusText
                : "Reloop API Error: {$status} {$statusText}";
        }
        parent::__construct($message, $status, $previous);
    }
}
