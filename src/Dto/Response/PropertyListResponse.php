<?php

namespace Reloop\Dto\Response;

final class PropertyListResponse
{
    /** @param list<ContactPropertyListItem> $properties */
    public function __construct(
        public string $object,
        public array $properties,
        public int $total,
        public int $page,
        public int $limit,
        public string $event,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            object: $data['object'],
            properties: array_map(
                static fn (array $property): ContactPropertyListItem => ContactPropertyListItem::fromArray($property),
                $data['properties'] ?? [],
            ),
            total: (int) $data['total'],
            page: (int) $data['page'],
            limit: (int) $data['limit'],
            event: $data['event'],
        );
    }
}
