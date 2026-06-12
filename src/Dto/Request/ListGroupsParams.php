<?php

namespace Reloop\Dto\Request;

use Reloop\Dto\Support\RequestPayload;

final class ListGroupsParams
{
    use RequestPayload;

    public function __construct(
        public ?int $page = null,
        public ?int $limit = null,
        public ?string $search = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return self::omitNull([
            'page' => $this->page,
            'limit' => $this->limit,
            'search' => $this->search,
        ]);
    }
}
