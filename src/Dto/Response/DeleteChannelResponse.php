<?php

namespace Reloop\Dto\Response;

final class DeleteChannelResponse
{
    public function __construct(
        public string $object,
        public bool $success,
        public string $id,
        public string $name,
        public string $event,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            object: $data['object'],
            success: (bool) $data['success'],
            id: $data['id'],
            name: $data['name'],
            event: $data['event'],
        );
    }
}
