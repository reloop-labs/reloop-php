<?php

namespace Reloop\Dto\Response;

final class ContactPropertyResponse extends ContactProperty
{
    public function __construct(
        string $object,
        string $id,
        string $propertyName,
        string $propertyType,
        ?string $defaultValue,
        string $createdAt,
        string $updatedAt,
        public string $event,
    ) {
        parent::__construct(
            $object,
            $id,
            $propertyName,
            $propertyType,
            $defaultValue,
            $createdAt,
            $updatedAt,
        );
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $property = ContactProperty::fromArray($data);

        return new self(
            object: $property->object,
            id: $property->id,
            propertyName: $property->propertyName,
            propertyType: $property->propertyType,
            defaultValue: $property->defaultValue,
            createdAt: $property->createdAt,
            updatedAt: $property->updatedAt,
            event: $data['event'],
        );
    }
}
