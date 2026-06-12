<?php

namespace Reloop\Dto\Response;

final class ChannelListResponse
{
    /** @param list<ContactChannelListItem> $channels */
    public function __construct(
        public string $object,
        public array $channels,
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
            channels: array_map(
                static fn (array $channel): ContactChannelListItem => ContactChannelListItem::fromArray($channel),
                $data['channels'] ?? [],
            ),
            total: (int) $data['total'],
            page: (int) $data['page'],
            limit: (int) $data['limit'],
            event: $data['event'],
        );
    }
}
