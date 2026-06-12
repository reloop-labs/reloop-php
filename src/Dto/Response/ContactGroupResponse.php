<?php

namespace Reloop\Dto\Response;

final class ContactGroupResponse extends ContactGroup
{
    public function __construct(
        string $object,
        string $id,
        string $name,
        string $createdAt,
        string $updatedAt,
        public string $event,
    ) {
        parent::__construct($object, $id, $name, $createdAt, $updatedAt);
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $group = ContactGroup::fromArray($data);

        return new self(
            object: $group->object,
            id: $group->id,
            name: $group->name,
            createdAt: $group->createdAt,
            updatedAt: $group->updatedAt,
            event: $data['event'],
        );
    }
}
