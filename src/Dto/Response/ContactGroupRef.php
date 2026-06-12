<?php

namespace Reloop\Dto\Response;

final class ContactGroupRef
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }

    /** @param array{id: string, name: string} $data */
    public static function fromArray(array $data): self
    {
        return new self($data['id'], $data['name']);
    }
}
