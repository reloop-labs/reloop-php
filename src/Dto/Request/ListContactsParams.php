<?php

namespace Reloop\Dto\Request;

use Reloop\Dto\Enum\ContactStatus;
use Reloop\Dto\Support\RequestPayload;

final class ListContactsParams
{
    use RequestPayload;

    public function __construct(
        public ?int $page = null,
        public ?int $limit = null,
        public ?string $search = null,
        public ?ContactStatus $status = null,
        public ?string $groupId = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return self::omitNull([
            'page' => $this->page,
            'limit' => $this->limit,
            'search' => $this->search,
            'status' => $this->status?->value,
            'groupId' => $this->groupId,
        ]);
    }
}
