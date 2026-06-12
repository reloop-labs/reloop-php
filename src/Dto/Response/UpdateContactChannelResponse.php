<?php

namespace Reloop\Dto\Response;

final class UpdateContactChannelResponse
{
    public function __construct(
        public bool $success,
        public string $status,
        public string $event,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            success: (bool) $data['success'],
            status: $data['status'],
            event: $data['event'],
        );
    }
}
