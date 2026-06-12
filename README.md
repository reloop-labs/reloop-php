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

Manage contacts, custom properties, groups, and channels via `$reloop->contacts`. All contact methods accept typed DTO classes (recommended) or plain arrays, and return typed response objects for full IDE autocomplete.

### Create a contact

```php
use Reloop\Dto\Enum\ContactStatus;
use Reloop\Dto\Request\CreateContactParams;

$contact = $reloop->contacts->create(new CreateContactParams(
    email: 'john.doe@example.com',
    firstName: 'John',
    lastName: 'Doe',
    status: ContactStatus::Subscribed,
    properties: ['company_name' => 'Acme'],
    groupIds: ['grp_123456789'],
));

echo $contact->id;
echo $contact->email;
echo $contact->event;
```

### List, get, update, and delete contacts

```php
use Reloop\Dto\Enum\ContactStatus;
use Reloop\Dto\Request\ListContactsParams;
use Reloop\Dto\Request\UpdateContactParams;

$list = $reloop->contacts->list(new ListContactsParams(page: 1, limit: 10));
foreach ($list->contacts as $contact) {
    echo $contact->email;
}

$contact = $reloop->contacts->get('cont_123456789');
echo $contact->firstName;

$updated = $reloop->contacts->update('cont_123456789', new UpdateContactParams(
    firstName: 'Jane',
    status: ContactStatus::Subscribed,
));

$result = $reloop->contacts->delete('cont_123456789');
echo $result->success;
```

### Custom properties

```php
use Reloop\Dto\Enum\PropertyType;
use Reloop\Dto\Request\CreatePropertyParams;
use Reloop\Dto\Request\ListPropertiesParams;
use Reloop\Dto\Request\UpdatePropertyParams;

$property = $reloop->contacts->createProperty(new CreatePropertyParams(
    name: 'company_name',
    type: PropertyType::String,
    fallbackValue: 'Unknown',
));

$properties = $reloop->contacts->listProperties(new ListPropertiesParams(page: 1, limit: 10));
$updatedProperty = $reloop->contacts->updateProperty(
    'prop_123456789',
    new UpdatePropertyParams(fallbackValue: 'N/A'),
);
$reloop->contacts->deleteProperty('prop_123456789');
```

### Groups

```php
use Reloop\Dto\Request\AddContactToGroupParams;
use Reloop\Dto\Request\CreateGroupParams;
use Reloop\Dto\Request\ListGroupsParams;
use Reloop\Dto\Request\RemoveContactFromGroupParams;
use Reloop\Dto\Request\UpdateGroupParams;

$group = $reloop->contacts->createGroup(new CreateGroupParams(name: 'Beta Testers'));
$groups = $reloop->contacts->listGroups(new ListGroupsParams(page: 1, limit: 10));
$oneGroup = $reloop->contacts->getGroup('grp_123456789');
$updatedGroup = $reloop->contacts->updateGroup('grp_123456789', new UpdateGroupParams(name: 'Early Access'));
$reloop->contacts->deleteGroup('grp_123456789');

$reloop->contacts->groups->addContact('grp_123456789', new AddContactToGroupParams(
    contact_id: 'cont_123456789',
));
$reloop->contacts->groups->removeContact('grp_123456789', new RemoveContactFromGroupParams(
    email: 'john.doe@example.com',
));
$groupContacts = $reloop->contacts->groups->listContacts('grp_123456789', ['page' => 1, 'limit' => 10]);
```

### Channels

```php
use Reloop\Dto\Enum\ChannelVisibility;
use Reloop\Dto\Enum\SubscriptionStatus;
use Reloop\Dto\Request\AddContactToChannelParams;
use Reloop\Dto\Request\CreateChannelParams;
use Reloop\Dto\Request\ListChannelsParams;
use Reloop\Dto\Request\UpdateChannelParams;
use Reloop\Dto\Request\UpdateContactChannelParams;

$channel = $reloop->contacts->channels->create(new CreateChannelParams(
    name: 'Product Updates',
    defaultSubscription: SubscriptionStatus::OptIn,
    visibility: ChannelVisibility::Public,
));

$channels = $reloop->contacts->channels->list(new ListChannelsParams(page: 1, limit: 10));
$oneChannel = $reloop->contacts->channels->get('chn_123456789');
$updatedChannel = $reloop->contacts->channels->update('chn_123456789', new UpdateChannelParams(
    name: 'Marketing News',
));
$reloop->contacts->channels->delete('chn_123456789');

$reloop->contacts->channels->addContact('chn_123456789', new AddContactToChannelParams(
    contact_id: 'cont_123456789',
    subscription: SubscriptionStatus::OptIn,
));
$reloop->contacts->channels->updateSubscription('chn_123456789', new UpdateContactChannelParams(
    contact_id: 'cont_123456789',
    subscription: SubscriptionStatus::OptIn,
));
```

## License

ISC
