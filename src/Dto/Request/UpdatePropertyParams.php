<?php

namespace Reloop\Dto\Request;

use Reloop\Dto\Support\RequestPayload;

final class UpdatePropertyParams
{
    use RequestPayload;

    public function __construct(
        public ?string $fallbackValue,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['fallbackValue' => $this->fallbackValue];
    }
}
