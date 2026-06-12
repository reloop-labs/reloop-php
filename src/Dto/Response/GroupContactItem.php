<?php

namespace Reloop\Dto\Response;

final class GroupContactItem
{
    /** @param array<string, string|int> $properties */
    public function __construct(
        public string $id,
        public string $email,
        public ?string $firstName,
        public ?string $lastName,
        public string $status,
        public array $properties,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            email: $data['email'],
            firstName: $data['firstName'] ?? null,
            lastName: $data['lastName'] ?? null,
            status: $data['status'],
            properties: $data['properties'] ?? [],
            createdAt: (string) $data['createdAt'],
            updatedAt: (string) $data['updatedAt'],
        );
    }
}
