<?php

namespace Reloop\Dto\Request;

use Reloop\Dto\Support\RequestPayload;

final class UpdateGroupParams
{
    use RequestPayload;

    public function __construct(
        public string $name,
    ) {
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return ['name' => $this->name];
    }
}
