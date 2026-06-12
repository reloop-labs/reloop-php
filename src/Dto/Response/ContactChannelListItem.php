<?php

namespace Reloop\Dto\Response;

final class ContactChannelListItem
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
        public string $defaultSubscription,
        public string $visibility,
        public string $createdAt,
        public string $updatedAt,
        public ?int $subscriberCount = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            defaultSubscription: $data['defaultSubscription'],
            visibility: $data['visibility'],
            createdAt: (string) $data['createdAt'],
            updatedAt: (string) $data['updatedAt'],
            subscriberCount: isset($data['subscriberCount']) ? (int) $data['subscriberCount'] : null,
        );
    }
}
