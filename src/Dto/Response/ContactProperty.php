<?php

namespace Reloop\Dto\Response;

final class ContactProperty
{
    public function __construct(
        public string $object,
        public string $id,
        public string $propertyName,
        public string $propertyType,
        public ?string $defaultValue,
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
            propertyName: $data['propertyName'],
            propertyType: $data['propertyType'],
            defaultValue: $data['defaultValue'] ?? null,
            createdAt: (string) $data['createdAt'],
            updatedAt: (string) $data['updatedAt'],
        );
    }
}
