<?php

namespace Reloop\Dto\Request;

use Reloop\Dto\Enum\PropertyType;
use Reloop\Dto\Support\RequestPayload;

final class CreatePropertyParams
{
    use RequestPayload;

    public function __construct(
        public string $name,
        public PropertyType $type,
        public ?string $fallbackValue = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return self::omitNull([
            'name' => $this->name,
            'type' => $this->type->value,
            'fallbackValue' => $this->fallbackValue,
        ]);
    }
}
