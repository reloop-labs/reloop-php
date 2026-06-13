<?php

namespace Reloop\Support;

final class Parameters
{
    /** @var array<string, string> */
    private const REQUEST_KEY_MAP = [
        'first_name' => 'firstName',
        'last_name' => 'lastName',
        'group_ids' => 'groupIds',
        'group_id' => 'groupId',
        'fallback_value' => 'fallbackValue',
        'default_subscription' => 'defaultSubscription',
        'channel_id' => 'channelId',
        'property_name' => 'propertyName',
        'property_type' => 'propertyType',
        'contact_id' => 'contact_id',
        'rate_limit_enabled' => 'rateLimitEnabled',
    ];

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    public static function forRequest(array $parameters): array
    {
        $normalized = [];

        foreach ($parameters as $key => $value) {
            if ($key === 'unsubscribed') {
                if (! array_key_exists('status', $parameters)) {
                    $normalized['status'] = $value ? 'unsubscribed' : 'subscribed';
                }

                continue;
            }

            $apiKey = self::REQUEST_KEY_MAP[$key] ?? self::toCamelCase($key);
            $normalized[$apiKey] = self::normalizeValue($value, forRequest: true);
        }

        return array_filter(
            $normalized,
            static fn (mixed $value): bool => $value !== null,
        );
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public static function forQuery(array $options): array
    {
        return self::forRequest($options);
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    public static function forSnakeRequest(array $parameters): array
    {
        return array_filter(
            $parameters,
            static fn (mixed $value): bool => $value !== null,
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function forResponse(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $normalized[self::toSnakeCase($key)] = self::normalizeValue($value, forRequest: false);
        }

        return $normalized;
    }

    private static function normalizeValue(mixed $value, bool $forRequest): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (self::isList($value)) {
            return array_map(
                static fn (mixed $item): mixed => is_array($item)
                    ? ($forRequest ? self::forRequest($item) : self::forResponse($item))
                    : $item,
                $value,
            );
        }

        return $forRequest ? self::forRequest($value) : self::forResponse($value);
    }

    /** @param array<int, mixed> $value */
    private static function isList(array $value): bool
    {
        if ($value === []) {
            return true;
        }

        return array_keys($value) === range(0, count($value) - 1);
    }

    private static function toCamelCase(string $key): string
    {
        if (! str_contains($key, '_')) {
            return $key;
        }

        return self::REQUEST_KEY_MAP[$key] ?? lcfirst(str_replace('_', '', ucwords($key, '_')));
    }

    private static function toSnakeCase(string $key): string
    {
        if (str_contains($key, '_')) {
            return $key;
        }

        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key) ?? $key);
    }
}
