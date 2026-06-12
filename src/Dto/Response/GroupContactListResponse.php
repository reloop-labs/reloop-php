<?php

namespace Reloop\Dto\Response;

final class GroupContactListResponse
{
    /**
     * @param array{id: string, name: string} $group
     * @param list<GroupContactItem> $contacts
     */
    public function __construct(
        public string $object,
        public array $group,
        public array $contacts,
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
            group: $data['group'],
            contacts: array_map(
                static fn (array $contact): GroupContactItem => GroupContactItem::fromArray($contact),
                $data['contacts'] ?? [],
            ),
            total: (int) $data['total'],
            page: (int) $data['page'],
            limit: (int) $data['limit'],
            event: $data['event'],
        );
    }
}
