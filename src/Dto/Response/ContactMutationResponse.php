<?php

namespace Reloop\Dto\Response;

final class ContactMutationResponse
{
    public function __construct(
        public bool $success,
        public string $object,
        public string $id,
        public string $event,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            success: (bool) $data['success'],
            object: $data['object'],
            id: $data['id'],
            event: $data['event'],
        );
    }
}
