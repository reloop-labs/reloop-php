<?php

namespace Reloop\Dto\Request;

use Reloop\Dto\Enum\ChannelVisibility;
use Reloop\Dto\Enum\SubscriptionStatus;
use Reloop\Dto\Support\RequestPayload;

final class CreateChannelParams
{
    use RequestPayload;

    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?SubscriptionStatus $defaultSubscription = null,
        public ?ChannelVisibility $visibility = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return self::omitNull([
            'name' => $this->name,
            'description' => $this->description,
            'defaultSubscription' => $this->defaultSubscription?->value,
            'visibility' => $this->visibility?->value,
        ]);
    }
}
