<?php

namespace Reloop\Dto\Response;

final class ContactGroup
{
    public function __construct(
        public string $object,
        public string $id,
        public string $name,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            object: $data['object'],
            id: $data['id'],
            name: $data['name'],
            createdAt: (string) $data['createdAt'],
            updatedAt: (string) $data['updatedAt'],
        );
    }
}
