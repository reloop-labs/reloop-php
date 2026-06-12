<?php

namespace Reloop\Dto\Support;

trait RequestPayload
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected static function omitNull(array $data): array
    {
        return array_filter($data, static fn (mixed $value): bool => $value !== null);
    }
}
