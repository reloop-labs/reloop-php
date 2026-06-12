<?php

namespace Reloop\Dto\Request;

use Reloop\Dto\Support\RequestPayload;

final class ListChannelsParams
{
    use RequestPayload;

    public function __construct(
        public ?int $page = null,
        public ?int $limit = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return self::omitNull([
            'page' => $this->page,
            'limit' => $this->limit,
        ]);
    }
}
