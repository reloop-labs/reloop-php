<?php

namespace Reloop\Support;

use Reloop\ApiKey;
use Reloop\ApiKeyList;
use Reloop\ChannelList;
use Reloop\Contact;
use Reloop\ContactChannel;
use Reloop\ContactGroup;
use Reloop\ContactList;
use Reloop\ContactProperty;
use Reloop\DnsRecord;
use Reloop\Domain;
use Reloop\DomainList;
use Reloop\DomainNameservers;
use Reloop\DomainStatus;
use Reloop\ForwardDnsResponse;
use Reloop\GroupList;
use Reloop\PropertyList;
use Reloop\Resource;

final class ResourceFactory
{
    /** @param array<string, mixed> $data */
    public static function apiKey(array $data): ApiKey
    {
        return ApiKey::from(Parameters::forResponse($data));
    }

    /** @param array<string, mixed> $data */
    public static function apiKeyList(array $data): ApiKeyList
    {
        $normalized = Parameters::forResponse($data);

        if (isset($normalized['api_keys']) && is_array($normalized['api_keys'])) {
            $normalized['api_keys'] = array_map(
                static fn (array $apiKey): ApiKey => ApiKey::from(Parameters::forResponse($apiKey)),
                $normalized['api_keys'],
            );
        }

        return ApiKeyList::from($normalized);
    }

    /** @param array<string, mixed> $data */
    public static function contact(array $data): Contact
    {
        return Contact::from(self::normalizeContact($data));
    }

    /** @param array<string, mixed> $data */
    public static function contactList(array $data): ContactList
    {
        $normalized = Parameters::forResponse($data);

        if (isset($normalized['contacts']) && is_array($normalized['contacts'])) {
            $normalized['contacts'] = array_map(
                static fn (array $contact): Contact => self::contact($contact),
                $normalized['contacts'],
            );
        }

        return ContactList::from($normalized);
    }

    /** @param array<string, mixed> $data */
    public static function contactProperty(array $data): ContactProperty
    {
        return ContactProperty::from(Parameters::forResponse($data));
    }

    /** @param array<string, mixed> $data */
    public static function propertyList(array $data): PropertyList
    {
        $normalized = Parameters::forResponse($data);

        if (isset($normalized['properties']) && is_array($normalized['properties'])) {
            $normalized['properties'] = array_map(
                static fn (array $property): ContactProperty => self::contactProperty($property),
                $normalized['properties'],
            );
        }

        return PropertyList::from($normalized);
    }

    /** @param array<string, mixed> $data */
    public static function contactGroup(array $data): ContactGroup
    {
        $normalized = Parameters::forResponse($data);

        if (isset($normalized['contacts']) && is_array($normalized['contacts'])) {
            $normalized['contacts'] = array_map(
                static fn (array $contact): Contact => self::contact($contact),
                $normalized['contacts'],
            );
        }

        return ContactGroup::from($normalized);
    }

    /** @param array<string, mixed> $data */
    public static function groupList(array $data): GroupList
    {
        $normalized = Parameters::forResponse($data);

        if (isset($normalized['groups']) && is_array($normalized['groups'])) {
            $normalized['groups'] = array_map(
                static fn (array $group): ContactGroup => ContactGroup::from(Parameters::forResponse($group)),
                $normalized['groups'],
            );
        }

        return GroupList::from($normalized);
    }

    /** @param array<string, mixed> $data */
    public static function contactChannel(array $data): ContactChannel
    {
        $normalized = Parameters::forResponse($data);

        if (isset($normalized['contact']) && is_array($normalized['contact'])) {
            $normalized['contact'] = self::contact($normalized['contact']);
        }

        return ContactChannel::from($normalized);
    }

    /** @param array<string, mixed> $data */
    public static function channelList(array $data): ChannelList
    {
        $normalized = Parameters::forResponse($data);

        if (isset($normalized['channels']) && is_array($normalized['channels'])) {
            $normalized['channels'] = array_map(
                static fn (array $channel): ContactChannel => ContactChannel::from(Parameters::forResponse($channel)),
                $normalized['channels'],
            );
        }

        return ChannelList::from($normalized);
    }

    /** @param array<string, mixed> $data */
    public static function dnsRecord(array $data): DnsRecord
    {
        return DnsRecord::from(Parameters::forResponse($data));
    }

    /** @param array<string, mixed> $data */
    public static function domain(array $data): Domain
    {
        $normalized = Parameters::forResponse($data);

        if (isset($normalized['dns_records']) && is_array($normalized['dns_records'])) {
            $normalized['dns_records'] = array_map(
                static fn (array $record): DnsRecord => self::dnsRecord($record),
                $normalized['dns_records'],
            );
        }

        return Domain::from($normalized);
    }

    /** @param array<string, mixed> $data */
    public static function domainList(array $data): DomainList
    {
        $normalized = Parameters::forResponse($data);

        if (isset($normalized['domains']) && is_array($normalized['domains'])) {
            $normalized['domains'] = array_map(
                static fn (array $domain): Domain => self::domain($domain),
                $normalized['domains'],
            );
        }

        return DomainList::from($normalized);
    }

    /** @param array<string, mixed> $data */
    public static function domainStatus(array $data): DomainStatus
    {
        return DomainStatus::from(Parameters::forResponse($data));
    }

    /** @param array<string, mixed> $data */
    public static function domainNameservers(array $data): DomainNameservers
    {
        return DomainNameservers::from(Parameters::forResponse($data));
    }

    /** @param array<string, mixed> $data */
    public static function forwardDnsResponse(array $data): ForwardDnsResponse
    {
        return ForwardDnsResponse::from(Parameters::forResponse($data));
    }

    /** @param array<string, mixed> $data */
    private static function normalizeContact(array $data): array
    {
        return Parameters::forResponse($data);
    }
}
