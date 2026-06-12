<?php

namespace Reloop\Dto\Response;

final class AddContactToChannelResponse
{
    public function __construct(
        public ContactResponse $contact,
        public string $subscriptionId,
        public string $event,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            contact: ContactResponse::fromArray($data['contact']),
            subscriptionId: $data['subscriptionId'],
            event: $data['event'],
        );
    }
}
