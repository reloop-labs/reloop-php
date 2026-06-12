<?php

namespace Reloop\Dto\Request;

use Reloop\Dto\Enum\SubscriptionStatus;
use Reloop\Dto\Support\RequestPayload;

final class ContactChannelInput
{
    use RequestPayload;

    public function __construct(
        public string $channelId,
        public SubscriptionStatus $subscription = SubscriptionStatus::OptIn,
    ) {
    }

    /** @return array{channelId: string, subscription: string} */
    public function toArray(): array
    {
        return [
            'channelId' => $this->channelId,
            'subscription' => $this->subscription->value,
        ];
    }
}
