<?php

namespace Reloop\Services;

use Reloop\Exceptions\ReloopValidationException;
use Reloop\ReloopClient;
use Reloop\Support\Validators;

class ContactPropertiesService
{
    private const PROPERTIES_BASE = '/api/contacts/v1/properties';

    public function __construct(private ReloopClient $client)
    {
    }

    /**
     * @param array{name?: string, type?: string, fallbackValue?: string|null} $params
     * @return array<string, mixed>
     */
    public function create(array $params): array
    {
        $body = self::validateCreateParams($params);

        return $this->client->request('POST', self::PROPERTIES_BASE . '/create', $body) ?? [];
    }

    /**
     * @param array{page?: int, limit?: int, search?: string, type?: string}|null $params
     * @return array<string, mixed>
     */
    public function list(?array $params = null): array
    {
        $query = self::validateListParams($params);

        return $this->client->request('GET', self::PROPERTIES_BASE . '/list', null, $query) ?? [];
    }

    /**
     * @param array{fallbackValue?: string|null} $params
     * @return array<string, mixed>
     */
    public function update(string $id, array $params = []): array
    {
        $propertyId = self::requirePropertyId($id, 'id');
        $body = self::validateUpdateParams($params);

        return $this->client->request('PATCH', self::PROPERTIES_BASE . '/' . $propertyId, $body) ?? [];
    }

    /** @return array<string, mixed> */
    public function delete(string $id): array
    {
        $propertyId = self::requirePropertyId($id, 'id');

        return $this->client->request('DELETE', self::PROPERTIES_BASE . '/' . $propertyId) ?? [];
    }

    private static function requirePropertyId(string $id, string $field): string
    {
        try {
            return Validators::requireNonEmptyString($id, $field);
        } catch (ReloopValidationException) {
            throw new ReloopValidationException(
                "Property {$field} is required and must be a non-empty string.",
                $field,
            );
        }
    }

    private static function requirePropertyName(?string $name, string $field): string
    {
        if ($name === null) {
            throw new ReloopValidationException(
                "Property {$field} is required and must be a string.",
                $field,
            );
        }
        $trimmed = trim($name);
        if (mb_strlen($trimmed) < 1) {
            throw new ReloopValidationException(
                "Property {$field} must be at least 1 character.",
                $field,
            );
        }
        if (mb_strlen($trimmed) > 255) {
            throw new ReloopValidationException(
                "Property {$field} must be at most 255 characters.",
                $field,
            );
        }

        return $trimmed;
    }

    private static function requirePropertyType(?string $type, string $field): string
    {
        if ($type === null || !in_array($type, ['string', 'number'], true)) {
            throw new ReloopValidationException(
                "Property {$field} must be \"string\" or \"number\".",
                $field,
            );
        }

        return $type;
    }

    /** @return array<string, mixed> */
    private static function validateCreateParams(array $params): array
    {
        $body = [
            'name' => self::requirePropertyName($params['name'] ?? null, 'name'),
            'type' => self::requirePropertyType($params['type'] ?? null, 'type'),
        ];
        if (array_key_exists('fallbackValue', $params)) {
            $body['fallbackValue'] = self::requireNullableFallbackValue($params['fallbackValue']);
        }

        return $body;
    }

    /** @return array<string, mixed> */
    private static function validateUpdateParams(array $params): array
    {
        return [
            'fallbackValue' => self::requireNullableFallbackValue($params['fallbackValue'] ?? null),
        ];
    }

    private static function requireNullableFallbackValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!is_string($value)) {
            throw new ReloopValidationException(
                'Property fallbackValue must be a string or null.',
                'fallbackValue',
            );
        }

        return $value;
    }

    /** @param array{page?: int, limit?: int, search?: string, type?: string}|null $params */
    private static function validateListParams(?array $params): array
    {
        $query = [];
        if ($params === null) {
            return $query;
        }
        if (isset($params['page'])) {
            Validators::requirePage((int) $params['page'], 'page');
            $query['page'] = (int) $params['page'];
        }
        if (isset($params['limit'])) {
            Validators::requireLimit((int) $params['limit'], 1, 100, 'limit');
            $query['limit'] = (int) $params['limit'];
        }
        if (isset($params['search'])) {
            $query['search'] = $params['search'];
        }
        if (isset($params['type'])) {
            self::requirePropertyType($params['type'], 'type');
            $query['type'] = $params['type'];
        }

        return $query;
    }
}
