<?php

namespace Reloop\Dto\Request;

use Reloop\Dto\Enum\ChannelVisibility;
use Reloop\Dto\Support\RequestPayload;

final class UpdateChannelParams
{
    use RequestPayload;

    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?ChannelVisibility $visibility = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return self::omitNull([
            'name' => $this->name,
            'description' => $this->description,
            'visibility' => $this->visibility?->value,
        ]);
    }
}
