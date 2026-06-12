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

$reloop = new Reloop('rl_your_api_key_here');
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

Manage contacts, custom properties, groups, and channels via `$reloop->contacts`.

### Create a contact

```php
$contact = $reloop->contacts->create([
    'email' => 'john.doe@example.com',
    'firstName' => 'John',
    'lastName' => 'Doe',
    'status' => 'subscribed',
    'properties' => ['company_name' => 'Acme'],
    'groupIds' => ['grp_123456789'],
]);
```

### List, get, update, and delete contacts

```php
$contacts = $reloop->contacts->list(['page' => 1, 'limit' => 10]);
$contact = $reloop->contacts->get('cont_123456789');
$updated = $reloop->contacts->update('cont_123456789', ['firstName' => 'Jane']);
$result = $reloop->contacts->delete('cont_123456789');
```

### Custom properties

```php
$property = $reloop->contacts->createProperty([
    'name' => 'company_name',
    'type' => 'string',
    'fallbackValue' => 'Unknown',
]);

$properties = $reloop->contacts->listProperties(['page' => 1, 'limit' => 10]);
$updatedProperty = $reloop->contacts->updateProperty('prop_123456789', ['fallbackValue' => 'N/A']);
$reloop->contacts->deleteProperty('prop_123456789');
```

### Groups

```php
$group = $reloop->contacts->createGroup(['name' => 'Beta Testers']);
$groups = $reloop->contacts->listGroups(['page' => 1, 'limit' => 10]);
$oneGroup = $reloop->contacts->getGroup('grp_123456789');
$updatedGroup = $reloop->contacts->updateGroup('grp_123456789', ['name' => 'Early Access']);
$reloop->contacts->deleteGroup('grp_123456789');

$reloop->contacts->groups->addContact('grp_123456789', ['contact_id' => 'cont_123456789']);
$reloop->contacts->groups->removeContact('grp_123456789', ['email' => 'john.doe@example.com']);
$groupContacts = $reloop->contacts->groups->listContacts('grp_123456789', ['page' => 1, 'limit' => 10]);
```

### Channels

```php
$channel = $reloop->contacts->channels->create([
    'name' => 'Product Updates',
    'defaultSubscription' => 'opt_in',
    'visibility' => 'public',
]);

$channels = $reloop->contacts->channels->list(['page' => 1, 'limit' => 10]);
$oneChannel = $reloop->contacts->channels->get('chn_123456789');
$updatedChannel = $reloop->contacts->channels->update('chn_123456789', ['name' => 'Marketing News']);
$reloop->contacts->channels->delete('chn_123456789');

$reloop->contacts->channels->addContact('chn_123456789', [
    'contact_id' => 'cont_123456789',
    'subscription' => 'opt_in',
]);
$reloop->contacts->channels->updateSubscription('chn_123456789', [
    'contact_id' => 'cont_123456789',
    'subscription' => 'opt_in',
]);
```

## License

ISC
