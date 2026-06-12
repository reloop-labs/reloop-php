<?php

namespace Reloop\Dto\Request;

use Reloop\Dto\Enum\ContactStatus;
use Reloop\Dto\Support\RequestPayload;

final class UpdateContactParams
{
    use RequestPayload;

    /** @param array<string, string|int>|null $properties */
    public function __construct(
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?ContactStatus $status = null,
        public ?array $properties = null,
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
        ]);
    }
}
