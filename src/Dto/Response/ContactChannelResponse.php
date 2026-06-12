<?php

namespace Reloop\Dto\Response;

final class ContactChannelResponse extends ContactChannel
{
    public function __construct(
        string $object,
        string $id,
        string $name,
        ?string $description,
        string $defaultSubscription,
        string $visibility,
        string $createdAt,
        string $updatedAt,
        public string $event,
    ) {
        parent::__construct(
            $object,
            $id,
            $name,
            $description,
            $defaultSubscription,
            $visibility,
            $createdAt,
            $updatedAt,
        );
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $channel = ContactChannel::fromArray($data);

        return new self(
            object: $channel->object,
            id: $channel->id,
            name: $channel->name,
            description: $channel->description,
            defaultSubscription: $channel->defaultSubscription,
            visibility: $channel->visibility,
            createdAt: $channel->createdAt,
            updatedAt: $channel->updatedAt,
            event: $data['event'],
        );
    }
}
