<?php

namespace Reloop\Dto\Response;

final class ContactResponse extends Contact
{
    /**
     * @param array<string, string|int> $properties
     * @param list<ContactGroupRef> $groups
     * @param list<ContactChannelRef> $channels
     */
    public function __construct(
        string $object,
        string $id,
        string $email,
        ?string $firstName,
        ?string $lastName,
        string $status,
        array $properties,
        array $groups,
        array $channels,
        ?string $suppressionReason,
        ?string $suppressedAt,
        string $createdAt,
        string $updatedAt,
        public string $event,
    ) {
        parent::__construct(
            $object,
            $id,
            $email,
            $firstName,
            $lastName,
            $status,
            $properties,
            $groups,
            $channels,
            $suppressionReason,
            $suppressedAt,
            $createdAt,
            $updatedAt,
        );
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $contact = Contact::fromArray($data);

        return new self(
            object: $contact->object,
            id: $contact->id,
            email: $contact->email,
            firstName: $contact->firstName,
            lastName: $contact->lastName,
            status: $contact->status,
            properties: $contact->properties,
            groups: $contact->groups,
            channels: $contact->channels,
            suppressionReason: $contact->suppressionReason,
            suppressedAt: $contact->suppressedAt,
            createdAt: $contact->createdAt,
            updatedAt: $contact->updatedAt,
            event: $data['event'],
        );
    }
}
