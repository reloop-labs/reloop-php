<?php

namespace Reloop\Dto\Response;

final class ContactChannelRef
{
    public function __construct(
        public string $id,
        public string $name,
        public string $subscription,
    ) {
    }

    /** @param array{id: string, name: string, subscription: string} $data */
    public static function fromArray(array $data): self
    {
        return new self($data['id'], $data['name'], $data['subscription']);
    }
}
