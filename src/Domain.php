<?php

namespace Reloop;

/**
 * @property string $object
 * @property string $id
 * @property string $domain
 * @property string $status
 * @property bool $user_verified_domain
 * @property bool $system_verified
 * @property string $custom_return_path
 * @property string $tracking_subdomain
 * @property bool $is_click_tracking_enabled
 * @property bool $is_open_tracking_enabled
 * @property string $tls
 * @property bool $is_tracking_domain
 * @property bool $is_sending_email_enabled
 * @property bool $is_receiving_email_enabled
 * @property string|null $verification_failed_reason
 * @property list<DnsRecord> $dns_records
 * @property string|null $last_verified_at
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $event
 */
class Domain extends Resource
{
}
