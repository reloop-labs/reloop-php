<?php

namespace Reloop\Dto\Request;

use Reloop\Dto\Enum\SubscriptionStatus;
use Reloop\Dto\Support\RequestPayload;

final class AddContactToChannelParams
{
    use RequestPayload;

    public function __construct(
        public ?string $contact_id = null,
        public ?string $email = null,
        public ?SubscriptionStatus $subscription = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return self::omitNull([
            'contact_id' => $this->contact_id,
            'email' => $this->email,
            'subscription' => $this->subscription?->value,
        ]);
    }
}
