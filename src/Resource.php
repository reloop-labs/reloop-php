<?php

namespace Reloop;

use BadMethodCallException;

class Resource implements \ArrayAccess
{
    /** @var array<string, mixed> */
    protected array $attributes = [];

    /** @param array<string, mixed> $attributes */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /** @param array<string, mixed> $attributes */
    public static function from(array $attributes): static
    {
        return new static($attributes);
    }

    /** @param array<string, mixed> $attributes */
    protected function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }

    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    /** @return array<string, mixed> */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->getAttributes();
    }

    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options) ?: '{}';
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function __get(string $name): mixed
    {
        return $this->getAttribute($name);
    }

    /** @return array<string, mixed> */
    public function __debugInfo(): array
    {
        return $this->getAttributes();
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->attributes);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('Cannot set resource attributes.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('Cannot unset resource attributes.');
    }
}
