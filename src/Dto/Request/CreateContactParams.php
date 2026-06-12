<?php

namespace Reloop\Dto\Request;

use Reloop\Dto\Enum\ContactStatus;
use Reloop\Dto\Support\RequestPayload;

final class CreateContactParams
{
    use RequestPayload;

    /**
     * @param array<string, string|int>|null $properties
     * @param list<string>|null $groupIds
     * @param list<ContactChannelInput>|null $channels
     */
    public function __construct(
        public string $email,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?ContactStatus $status = null,
        public ?array $properties = null,
        public ?array $groupIds = null,
        public ?array $channels = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return self::omitNull([
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'status' => $this->status?->value,
            'properties' => $this->properties,
            'groupIds' => $this->groupIds,
            'channels' => $this->channels !== null
                ? array_map(static fn (ContactChannelInput $channel): array => $channel->toArray(), $this->channels)
                : null,
        ]);
    }
}
