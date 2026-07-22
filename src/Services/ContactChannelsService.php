<?php

namespace Reloop\Services;

use Reloop\Exceptions\ReloopValidationException;
use Reloop\ReloopClient;
use Reloop\Support\Validators;

class ContactChannelsService
{
    private const CHANNELS_BASE = '/api/contacts/v1/channels';
    private const CHANNEL_MEMBERSHIP = '/api/contacts/channel';

    public function __construct(private ReloopClient $client)
    {
    }

    /**
     * @param array{
     *   name?: string,
     *   description?: string,
     *   defaultSubscription?: string,
     *   visibility?: string
     * } $params
     * @return array<string, mixed>
     */
    public function create(array $params): array
    {
        $body = self::validateCreateParams($params);

        return $this->client->request('POST', self::CHANNELS_BASE . '/create', $body) ?? [];
    }

    /**
     * @param array{page?: int, limit?: int}|null $params
     * @return array<string, mixed>
     */
    public function list(?array $params = null): array
    {
        $query = self::validateListParams($params);

        return $this->client->request('GET', self::CHANNELS_BASE . '/list', null, $query) ?? [];
    }

    /** @return array<string, mixed> */
    public function get(string $id): array
    {
        $channelId = self::requireChannelId($id, 'id');

        return $this->client->request('GET', self::CHANNELS_BASE . '/' . $channelId) ?? [];
    }

    /**
     * @param array{name?: string, description?: string|null, visibility?: string} $params
     * @return array<string, mixed>
     */
    public function update(string $id, array $params): array
    {
        $channelId = self::requireChannelId($id, 'id');
        $body = self::validateUpdateParams($params);

        return $this->client->request('PATCH', self::CHANNELS_BASE . '/' . $channelId, $body) ?? [];
    }

    /** @return array<string, mixed> */
    public function delete(string $id): array
    {
        $channelId = self::requireChannelId($id, 'id');

        return $this->client->request('DELETE', self::CHANNELS_BASE . '/' . $channelId) ?? [];
    }

    /**
     * @param array{contact_id?: string, email?: string, subscription?: string} $params
     * @return array<string, mixed>
     */
    public function addContact(string $id, array $params): array
    {
        $channelId = self::requireChannelId($id, 'id');
        $body = self::validateAddContactParams($params);

        return $this->client->request('POST', self::CHANNEL_MEMBERSHIP . '/' . $channelId, $body) ?? [];
    }

    /**
     * @param array{contact_id?: string, email?: string, subscription?: string} $params
     * @return array<string, mixed>
     */
    public function updateSubscription(string $id, array $params): array
    {
        $channelId = self::requireChannelId($id, 'id');
        $body = self::validateUpdateSubscriptionParams($params);

        return $this->client->request('PATCH', self::CHANNEL_MEMBERSHIP . '/' . $channelId, $body) ?? [];
    }

    private static function requireChannelId(string $id, string $field): string
    {
        try {
            return Validators::requireNonEmptyString($id, $field);
        } catch (ReloopValidationException) {
            throw new ReloopValidationException(
                "Channel {$field} is required and must be a non-empty string.",
                $field,
            );
        }
    }

    private static function requireChannelName(?string $name, string $field): string
    {
        if ($name === null) {
            throw new ReloopValidationException(
                "Channel {$field} is required and must be a string.",
                $field,
            );
        }
        $trimmed = trim($name);
        if (mb_strlen($trimmed) < 1) {
            throw new ReloopValidationException(
                "Channel {$field} must be at least 1 character.",
                $field,
            );
        }
        if (mb_strlen($trimmed) > 255) {
            throw new ReloopValidationException(
                "Channel {$field} must be at most 255 characters.",
                $field,
            );
        }

        return $trimmed;
    }

    private static function requireOptionalDescription(?string $description, string $field): void
    {
        if ($description !== null && mb_strlen($description) > 1000) {
            throw new ReloopValidationException(
                "Channel {$field} must be at most 1000 characters.",
                $field,
            );
        }
    }

    private static function requireOptionalSubscription(?string $subscription, string $field): void
    {
        if ($subscription !== null && !in_array($subscription, ['opt_in', 'opt_out'], true)) {
            throw new ReloopValidationException(
                "Channel {$field} must be \"opt_in\" or \"opt_out\".",
                $field,
            );
        }
    }

    private static function requireSubscription(?string $subscription, string $field): string
    {
        self::requireOptionalSubscription($subscription, $field);
        if ($subscription === null) {
            throw new ReloopValidationException(
                "Channel {$field} is required and must be \"opt_in\" or \"opt_out\".",
                $field,
            );
        }

        return $subscription;
    }

    private static function requireOptionalVisibility(?string $visibility, string $field): void
    {
        if ($visibility !== null && !in_array($visibility, ['private', 'public'], true)) {
            throw new ReloopValidationException(
                "Channel {$field} must be \"private\" or \"public\".",
                $field,
            );
        }
    }

    /** @return array<string, string> */
    private static function validateMembershipParams(array $params): array
    {
        $body = [];

        if (array_key_exists('contact_id', $params)) {
            try {
                $body['contact_id'] = Validators::requireNonEmptyString($params['contact_id'], 'contact_id');
            } catch (ReloopValidationException) {
                throw new ReloopValidationException(
                    'contact_id must be a non-empty string when provided.',
                    'contact_id',
                );
            }
        }
        if (array_key_exists('email', $params)) {
            try {
                $body['email'] = Validators::requireNonEmptyString($params['email'], 'email');
            } catch (ReloopValidationException) {
                throw new ReloopValidationException(
                    'email must be a non-empty string when provided.',
                    'email',
                );
            }
        }
        if ($body === []) {
            throw new ReloopValidationException('Either contact_id or email is required.', 'params');
        }

        return $body;
    }

    /** @return array<string, mixed> */
    private static function validateCreateParams(array $params): array
    {
        $body = ['name' => self::requireChannelName($params['name'] ?? null, 'name')];

        if (array_key_exists('description', $params)) {
            self::requireOptionalDescription($params['description'], 'description');
            $body['description'] = $params['description'];
        }
        if (array_key_exists('defaultSubscription', $params)) {
            self::requireOptionalSubscription($params['defaultSubscription'], 'defaultSubscription');
            $body['defaultSubscription'] = $params['defaultSubscription'];
        }
        if (array_key_exists('visibility', $params)) {
            self::requireOptionalVisibility($params['visibility'], 'visibility');
            $body['visibility'] = $params['visibility'];
        }

        return $body;
    }

    /** @return array<string, mixed> */
    private static function validateUpdateParams(array $params): array
    {
        $body = [];

        if (array_key_exists('name', $params)) {
            $body['name'] = self::requireChannelName($params['name'], 'name');
        }
        if (array_key_exists('description', $params)) {
            if ($params['description'] !== null) {
                self::requireOptionalDescription($params['description'], 'description');
            }
            $body['description'] = $params['description'];
        }
        if (array_key_exists('visibility', $params)) {
            self::requireOptionalVisibility($params['visibility'], 'visibility');
            $body['visibility'] = $params['visibility'];
        }
        if ($body === []) {
            throw new ReloopValidationException(
                'update requires at least one of name, description, or visibility.',
                'params',
            );
        }

        return $body;
    }

    /** @return array<string, mixed> */
    private static function validateAddContactParams(array $params): array
    {
        $body = self::validateMembershipParams($params);
        if (array_key_exists('subscription', $params)) {
            self::requireOptionalSubscription($params['subscription'], 'subscription');
            $body['subscription'] = $params['subscription'];
        }

        return $body;
    }

    /** @return array<string, mixed> */
    private static function validateUpdateSubscriptionParams(array $params): array
    {
        $body = self::validateMembershipParams($params);
        $body['subscription'] = self::requireSubscription($params['subscription'] ?? null, 'subscription');

        return $body;
    }

    /** @param array{page?: int, limit?: int}|null $params */
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

        return $query;
    }
}
