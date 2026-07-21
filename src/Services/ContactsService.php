<?php

namespace Reloop\Services;

use Reloop\Exceptions\ReloopValidationException;
use Reloop\ReloopClient;
use Reloop\Support\Validators;

class ContactsService
{
    private const CONTACTS_BASE = '/api/contacts';
    private const EMAIL_PATTERN = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    private const CREATE_PROPERTY_KEY_PATTERN = '/^[a-z0-9_]+$/';
    private const UPDATE_PROPERTY_KEY_PATTERN = '/^[a-z_]+$/';

    public ContactPropertiesService $properties;
    public ContactGroupsService $groups;
    public ContactChannelsService $channels;

    public function __construct(private ReloopClient $client)
    {
        $this->properties = new ContactPropertiesService($client);
        $this->groups = new ContactGroupsService($client);
        $this->channels = new ContactChannelsService($client);
    }

    /**
     * @param array{
     *   email?: string,
     *   firstName?: string,
     *   lastName?: string,
     *   status?: string,
     *   properties?: array<string, string|int|float>,
     *   groupIds?: list<string>,
     *   channels?: list<array{channelId: string, subscription: string}>
     * } $params
     * @return array<string, mixed>
     */
    public function create(array $params): array
    {
        $body = self::validateCreateParams($params);

        return $this->client->request('POST', self::CONTACTS_BASE . '/create', $body) ?? [];
    }

    /** @return array<string, mixed> */
    public function get(string $id): array
    {
        $contactId = self::requireContactId($id, 'id');

        return $this->client->request('GET', self::CONTACTS_BASE . '/retrieve/' . $contactId) ?? [];
    }

    /**
     * @param array{page?: int, limit?: int, search?: string, status?: string}|null $params
     * @return array<string, mixed>
     */
    public function list(?array $params = null): array
    {
        $query = self::validateListParams($params);

        return $this->client->request('GET', self::CONTACTS_BASE . '/list', null, $query) ?? [];
    }

    /**
     * @param array{
     *   email?: string,
     *   firstName?: string,
     *   lastName?: string,
     *   status?: string,
     *   properties?: array<string, string|int|float>
     * } $params
     * @return array<string, mixed>
     */
    public function update(string $id, array $params): array
    {
        $contactId = self::requireContactId($id, 'id');
        $body = self::validateUpdateParams($params);

        return $this->client->request('PATCH', self::CONTACTS_BASE . '/' . $contactId, $body) ?? [];
    }

    /** @return array<string, mixed> */
    public function delete(string $id): array
    {
        $contactId = self::requireContactId($id, 'id');

        return $this->client->request('DELETE', self::CONTACTS_BASE . '/' . $contactId) ?? [];
    }

    private static function requireContactId(string $id, string $field): string
    {
        try {
            return Validators::requireNonEmptyString($id, $field);
        } catch (ReloopValidationException) {
            throw new ReloopValidationException(
                "Contact {$field} is required and must be a non-empty string.",
                $field,
            );
        }
    }

    private static function requireContactEmail(?string $email, string $field): string
    {
        if ($email === null) {
            throw new ReloopValidationException(
                "Contact {$field} is required and must be a string.",
                $field,
            );
        }
        $trimmed = trim($email);
        if ($trimmed === '') {
            throw new ReloopValidationException(
                "Contact {$field} is required and must be a string.",
                $field,
            );
        }
        if (!preg_match(self::EMAIL_PATTERN, $trimmed)) {
            throw new ReloopValidationException(
                "Contact {$field} must be a valid email address.",
                $field,
            );
        }

        return $trimmed;
    }

    private static function requireContactStatus(string $status, string $field): void
    {
        if (!in_array($status, ['subscribed', 'unsubscribed', 'blocked'], true)) {
            throw new ReloopValidationException(
                "{$field} must be \"subscribed\", \"unsubscribed\", or \"blocked\".",
                $field,
            );
        }
    }

    /**
     * @param array<string, string|int|float>|null $properties
     * @return array<string, string|int|float>|null
     */
    private static function validateProperties(?array $properties, string $keyPattern, string $field): ?array
    {
        if ($properties === null) {
            return null;
        }
        $out = [];
        foreach ($properties as $key => $value) {
            if (!preg_match($keyPattern, (string) $key)) {
                throw new ReloopValidationException(
                    "{$field} keys must match {$keyPattern}.",
                    $field,
                );
            }
            if (!is_string($value) && !is_int($value) && !is_float($value)) {
                throw new ReloopValidationException(
                    "{$field}.{$key} must be a string or number.",
                    $field,
                );
            }
            $out[$key] = $value;
        }

        return $out;
    }

    /** @param list<string>|null $groupIds */
    private static function validateGroupIds(?array $groupIds): ?array
    {
        if ($groupIds === null) {
            return null;
        }
        $out = [];
        foreach ($groupIds as $groupId) {
            try {
                $out[] = Validators::requireNonEmptyString($groupId, 'groupIds');
            } catch (ReloopValidationException) {
                throw new ReloopValidationException(
                    'groupIds must contain non-empty strings.',
                    'groupIds',
                );
            }
        }

        return $out;
    }

    /** @param list<array{channelId?: string, subscription?: string}>|null $channels */
    private static function validateChannels(?array $channels): ?array
    {
        if ($channels === null) {
            return null;
        }
        $out = [];
        foreach ($channels as $i => $entry) {
            if (!is_array($entry)) {
                throw new ReloopValidationException("channels[{$i}] must be an object.", 'channels');
            }
            $channelId = $entry['channelId'] ?? null;
            if (!is_string($channelId) || trim($channelId) === '') {
                throw new ReloopValidationException(
                    "channels[{$i}].channelId must be a non-empty string.",
                    'channels',
                );
            }
            $subscription = $entry['subscription'] ?? null;
            if (!in_array($subscription, ['opt_in', 'opt_out'], true)) {
                throw new ReloopValidationException(
                    "channels[{$i}].subscription must be \"opt_in\" or \"opt_out\".",
                    'channels',
                );
            }
            $out[] = [
                'channelId' => trim($channelId),
                'subscription' => $subscription,
            ];
        }

        return $out;
    }

    /** @return array<string, mixed> */
    private static function validateCreateParams(array $params): array
    {
        $body = ['email' => self::requireContactEmail($params['email'] ?? null, 'email')];

        if (array_key_exists('firstName', $params)) {
            $body['firstName'] = $params['firstName'];
        }
        if (array_key_exists('lastName', $params)) {
            $body['lastName'] = $params['lastName'];
        }
        if (array_key_exists('status', $params)) {
            self::requireContactStatus((string) $params['status'], 'status');
            $body['status'] = $params['status'];
        }

        $properties = self::validateProperties(
            $params['properties'] ?? null,
            self::CREATE_PROPERTY_KEY_PATTERN,
            'properties',
        );
        if ($properties !== null) {
            $body['properties'] = $properties;
        }

        $groupIds = self::validateGroupIds($params['groupIds'] ?? null);
        if ($groupIds !== null) {
            $body['groupIds'] = $groupIds;
        }

        $channels = self::validateChannels($params['channels'] ?? null);
        if ($channels !== null) {
            $body['channels'] = $channels;
        }

        return $body;
    }

    /** @return array<string, mixed> */
    private static function validateUpdateParams(array $params): array
    {
        $body = [];

        if (array_key_exists('email', $params)) {
            $body['email'] = self::requireContactEmail($params['email'], 'email');
        }
        if (array_key_exists('firstName', $params)) {
            $body['firstName'] = $params['firstName'];
        }
        if (array_key_exists('lastName', $params)) {
            $body['lastName'] = $params['lastName'];
        }
        if (array_key_exists('status', $params)) {
            self::requireContactStatus((string) $params['status'], 'status');
            $body['status'] = $params['status'];
        }

        $properties = self::validateProperties(
            $params['properties'] ?? null,
            self::UPDATE_PROPERTY_KEY_PATTERN,
            'properties',
        );
        if ($properties !== null) {
            $body['properties'] = $properties;
        }

        if ($body === []) {
            throw new ReloopValidationException(
                'update requires at least one of email, firstName, lastName, status, or properties.',
                'params',
            );
        }

        return $body;
    }

    /** @param array{page?: int, limit?: int, search?: string, status?: string}|null $params */
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
        if (isset($params['status'])) {
            self::requireContactStatus((string) $params['status'], 'status');
            $query['status'] = $params['status'];
        }

        return $query;
    }
}
