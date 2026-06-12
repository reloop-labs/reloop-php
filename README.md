# Reloop PHP SDK

The official PHP SDK for [Reloop](https://reloop.sh), providing a convenient, PSR-4 compliant wrapper around the Reloop REST API.

## Requirements
- PHP 8.1 or higher
- Composer

## Installation

Install the package via Composer:

```bash
composer require reloop/reloop-email
```

## Getting Started

Initialize the client with your API key. You can find or generate your API key in the Reloop Dashboard.

```php
require_once 'vendor/autoload.php';

use Reloop\Reloop;

$reloop = Reloop::client('rl_your_api_key_here');
```

## API Key Management

The SDK supports full CRUD and lifecycle management operations for API Keys.

### List API Keys
```php
$response = $reloop->apiKeys->list(['page' => 1, 'limit' => 10]);
print_r($response['apiKeys']); // Array of API keys
print_r($response['total']); // Total count
```

### Create an API Key
```php
$newKey = $reloop->apiKeys->create(['name' => 'Production Key']);
echo $newKey['key']; // The actual secret key (only returned on creation or rotation)
```

### Get an API Key
```php
$key = $reloop->apiKeys->get('api_key_id_here');
```

### Update an API Key
```php
$updatedKey = $reloop->apiKeys->update('api_key_id_here', ['name' => 'New Name']);
```

### Delete an API Key
```php
$reloop->apiKeys->delete('api_key_id_here');
```

### Lifecycle Operations
```php
// Rotate the secret of an API key while keeping the same ID
$rotatedKey = $reloop->apiKeys->rotate('api_key_id_here');

// Temporarily disable an API key
$reloop->apiKeys->disable('api_key_id_here');

// Re-enable an API key
$reloop->apiKeys->enable('api_key_id_here');
```

## Contacts

Manage contacts, custom properties, groups, and channels via `$reloop->contacts`. Methods accept snake_case `parameters` and `options` arrays and return typed resource objects with snake_case properties.

### Create a contact

```php
$reloop = Reloop::client('re_123456789');

$contact = $reloop->contacts->create(
    parameters: [
        'email' => 'steve.wozniak@gmail.com',
        'first_name' => 'Steve',
        'last_name' => 'Wozniak',
        'unsubscribed' => false,
        'properties' => [
            'company' => 'Reloop',
        ],
        'group_ids' => ['grp_123456789'],
    ],
);

echo $contact->id;
echo $contact->email;
echo $contact->first_name;
```

You can also pass Reloop-specific fields directly:

```php
$contact = $reloop->contacts->create(
    parameters: [
        'email' => 'john.doe@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'status' => 'subscribed',
        'channels' => [
            [
                'channel_id' => 'chn_123456789',
                'subscription' => 'opt_in',
            ],
        ],
    ],
);
```

### List, get, update, and delete contacts

```php
$list = $reloop->contacts->list(options: [
    'page' => 1,
    'limit' => 10,
]);

foreach ($list->contacts as $contact) {
    echo $contact->email;
}

$contact = $reloop->contacts->get('cont_123456789');
echo $contact->first_name;

$updated = $reloop->contacts->update(
    'cont_123456789',
    parameters: [
        'first_name' => 'Jane',
        'unsubscribed' => false,
    ],
);

$result = $reloop->contacts->delete('cont_123456789');
echo $result->success;
```

### Custom properties

```php
$property = $reloop->contacts->createProperty(
    parameters: [
        'name' => 'company_name',
        'type' => 'string',
        'fallback_value' => 'Unknown',
    ],
);

$properties = $reloop->contacts->listProperties(options: [
    'page' => 1,
    'limit' => 10,
]);

$updatedProperty = $reloop->contacts->updateProperty(
    'prop_123456789',
    parameters: [
        'fallback_value' => 'N/A',
    ],
);

$reloop->contacts->deleteProperty('prop_123456789');
```

### Groups

```php
$group = $reloop->contacts->createGroup(
    parameters: ['name' => 'Beta Testers'],
);

$groups = $reloop->contacts->listGroups(options: [
    'page' => 1,
    'limit' => 10,
]);

$oneGroup = $reloop->contacts->getGroup('grp_123456789');
$updatedGroup = $reloop->contacts->updateGroup(
    'grp_123456789',
    parameters: ['name' => 'Early Access'],
);
$reloop->contacts->deleteGroup('grp_123456789');

$reloop->contacts->groups->addContact(
    'grp_123456789',
    parameters: ['contact_id' => 'cont_123456789'],
);
$reloop->contacts->groups->removeContact(
    'grp_123456789',
    parameters: ['email' => 'john.doe@example.com'],
);
$groupContacts = $reloop->contacts->groups->listContacts(
    'grp_123456789',
    options: ['page' => 1, 'limit' => 10],
);
```

### Channels

```php
$channel = $reloop->contacts->channels->create(
    parameters: [
        'name' => 'Product Updates',
        'default_subscription' => 'opt_in',
        'visibility' => 'public',
    ],
);

$channels = $reloop->contacts->channels->list(options: [
    'page' => 1,
    'limit' => 10,
]);

$oneChannel = $reloop->contacts->channels->get('chn_123456789');
$updatedChannel = $reloop->contacts->channels->update(
    'chn_123456789',
    parameters: ['name' => 'Marketing News'],
);
$reloop->contacts->channels->delete('chn_123456789');

$reloop->contacts->channels->addContact(
    'chn_123456789',
    parameters: [
        'contact_id' => 'cont_123456789',
        'subscription' => 'opt_in',
    ],
);
$reloop->contacts->channels->updateSubscription(
    'chn_123456789',
    parameters: [
        'contact_id' => 'cont_123456789',
        'subscription' => 'opt_in',
    ],
);
```

## License

ISC
