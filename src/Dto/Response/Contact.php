<?php

namespace Reloop\Dto\Response;

final class Contact
{
    /**
     * @param array<string, string|int> $properties
     * @param list<ContactGroupRef> $groups
     * @param list<ContactChannelRef> $channels
     */
    public function __construct(
        public string $object,
        public string $id,
        public string $email,
        public ?string $firstName,
        public ?string $lastName,
        public string $status,
        public array $properties,
        public array $groups,
        public array $channels,
        public ?string $suppressionReason,
        public ?string $suppressedAt,
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
            email: $data['email'],
            firstName: $data['firstName'] ?? null,
            lastName: $data['lastName'] ?? null,
            status: $data['status'],
            properties: $data['properties'] ?? [],
            groups: array_map(
                static fn (array $group): ContactGroupRef => ContactGroupRef::fromArray($group),
                $data['groups'] ?? [],
            ),
            channels: array_map(
                static fn (array $channel): ContactChannelRef => ContactChannelRef::fromArray($channel),
                $data['channels'] ?? [],
            ),
            suppressionReason: $data['suppressionReason'] ?? null,
            suppressedAt: isset($data['suppressedAt']) ? (string) $data['suppressedAt'] : null,
            createdAt: (string) $data['createdAt'],
            updatedAt: (string) $data['updatedAt'],
        );
    }
}
