<?php

namespace Reloop\Dto\Request;

use Reloop\Dto\Enum\SubscriptionStatus;
use Reloop\Dto\Support\RequestPayload;

final class ContactChannelInput
{
    use RequestPayload;

    public function __construct(
        public string $channelId,
        public SubscriptionStatus $subscription = SubscriptionStatus::OptIn,
    ) {
    }

    /**
     * @return array{channelId: string, subscription: string}
     */
    public function toArray(): array
    {
        return [
            'channelId' => $this->channelId,
            'subscription' => $this->subscription->value,
        ];
    }
}

final class CreateContactParams
{
    use RequestPayload;

    /**
     * @param array<string, string|int>|null $properties
     * @param list<string>|null $groupIds
     * @param list<ContactChannelInput>|null $channels
     */
    public function __construct(
        public string $email,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?\Reloop\Dto\Enum\ContactStatus $status = null,
        public ?array $properties = null,
        public ?array $groupIds = null,
        public ?array $channels = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::omitNull([
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'status' => $this->status?->value,
            'properties' => $this->properties,
            'groupIds' => $this->groupIds,
            'channels' => $this->channels !== null
                ? array_map(static fn (ContactChannelInput $channel): array => $channel->toArray(), $this->channels)
                : null,
        ]);
    }
}

final class UpdateContactParams
{
    use RequestPayload;

    /**
     * @param array<string, string|int>|null $properties
     */
    public function __construct(
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?\Reloop\Dto\Enum\ContactStatus $status = null,
        public ?array $properties = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::omitNull([
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'status' => $this->status?->value,
            'properties' => $this->properties,
        ]);
    }
}

final class ListContactsParams
{
    use RequestPayload;

    public function __construct(
        public ?int $page = null,
        public ?int $limit = null,
        public ?string $search = null,
        public ?\Reloop\Dto\Enum\ContactStatus $status = null,
        public ?string $groupId = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::omitNull([
            'page' => $this->page,
            'limit' => $this->limit,
            'search' => $this->search,
            'status' => $this->status?->value,
            'groupId' => $this->groupId,
        ]);
    }
}

final class CreatePropertyParams
{
    use RequestPayload;

    public function __construct(
        public string $name,
        public \Reloop\Dto\Enum\PropertyType $type,
        public ?string $fallbackValue = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::omitNull([
            'name' => $this->name,
            'type' => $this->type->value,
            'fallbackValue' => $this->fallbackValue,
        ]);
    }
}

final class UpdatePropertyParams
{
    use RequestPayload;

    public function __construct(
        public ?string $fallbackValue,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['fallbackValue' => $this->fallbackValue];
    }
}

final class ListPropertiesParams
{
    use RequestPayload;

    public function __construct(
        public ?int $page = null,
        public ?int $limit = null,
        public ?string $search = null,
        public ?\Reloop\Dto\Enum\PropertyType $type = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::omitNull([
            'page' => $this->page,
            'limit' => $this->limit,
            'search' => $this->search,
            'type' => $this->type?->value,
        ]);
    }
}

final class CreateGroupParams
{
    use RequestPayload;

    public function __construct(
        public string $name,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['name' => $this->name];
    }
}

final class UpdateGroupParams
{
    use RequestPayload;

    public function __construct(
        public string $name,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['name' => $this->name];
    }
}

final class ListGroupsParams
{
    use RequestPayload;

    public function __construct(
        public ?int $page = null,
        public ?int $limit = null,
        public ?string $search = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::omitNull([
            'page' => $this->page,
            'limit' => $this->limit,
            'search' => $this->search,
        ]);
    }
}

final class AddContactToGroupParams
{
    use RequestPayload;

    public function __construct(
        public ?string $contact_id = null,
        public ?string $email = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::omitNull([
            'contact_id' => $this->contact_id,
            'email' => $this->email,
        ]);
    }
}

final class RemoveContactFromGroupParams
{
    use RequestPayload;

    public function __construct(
        public ?string $contact_id = null,
        public ?string $email = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::omitNull([
            'contact_id' => $this->contact_id,
            'email' => $this->email,
        ]);
    }
}

final class CreateChannelParams
{
    use RequestPayload;

    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?\Reloop\Dto\Enum\SubscriptionStatus $defaultSubscription = null,
        public ?\Reloop\Dto\Enum\ChannelVisibility $visibility = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::omitNull([
            'name' => $this->name,
            'description' => $this->description,
            'defaultSubscription' => $this->defaultSubscription?->value,
            'visibility' => $this->visibility?->value,
        ]);
    }
}

final class UpdateChannelParams
{
    use RequestPayload;

    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?\Reloop\Dto\Enum\ChannelVisibility $visibility = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::omitNull([
            'name' => $this->name,
            'description' => $this->description,
            'visibility' => $this->visibility?->value,
        ]);
    }
}

final class ListChannelsParams
{
    use RequestPayload;

    public function __construct(
        public ?int $page = null,
        public ?int $limit = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::omitNull([
            'page' => $this->page,
            'limit' => $this->limit,
        ]);
    }
}

final class AddContactToChannelParams
{
    use RequestPayload;

    public function __construct(
        public ?string $contact_id = null,
        public ?string $email = null,
        public ?\Reloop\Dto\Enum\SubscriptionStatus $subscription = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::omitNull([
            'contact_id' => $this->contact_id,
            'email' => $this->email,
            'subscription' => $this->subscription?->value,
        ]);
    }
}

final class UpdateContactChannelParams
{
    use RequestPayload;

    public function __construct(
        public \Reloop\Dto\Enum\SubscriptionStatus $subscription,
        public ?string $contact_id = null,
        public ?string $email = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::omitNull([
            'contact_id' => $this->contact_id,
            'email' => $this->email,
            'subscription' => $this->subscription->value,
        ]);
    }
}
