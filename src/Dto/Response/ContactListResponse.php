<?php

namespace Reloop\Dto\Response;

final class ContactListResponse
{
    /** @param list<Contact> $contacts */
    public function __construct(
        public string $object,
        public array $contacts,
        public int $total,
        public int $page,
        public int $limit,
        public int $totalContacts,
        public int $subscribedContacts,
        public int $unsubscribedContacts,
        public string $event,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            object: $data['object'],
            contacts: array_map(
                static fn (array $contact): Contact => Contact::fromArray($contact),
                $data['contacts'] ?? [],
            ),
            total: (int) $data['total'],
            page: (int) $data['page'],
            limit: (int) $data['limit'],
            totalContacts: (int) $data['totalContacts'],
            subscribedContacts: (int) $data['subscribedContacts'],
            unsubscribedContacts: (int) $data['unsubscribedContacts'],
            event: $data['event'],
        );
    }
}
