<?php

namespace Reloop\Dto\Response;

final class GroupListResponse
{
    /** @param list<ContactGroupListItem> $groups */
    public function __construct(
        public string $object,
        public array $groups,
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
            groups: array_map(
                static fn (array $group): ContactGroupListItem => ContactGroupListItem::fromArray($group),
                $data['groups'] ?? [],
            ),
            total: (int) $data['total'],
            page: (int) $data['page'],
            limit: (int) $data['limit'],
            event: $data['event'],
        );
    }
}
