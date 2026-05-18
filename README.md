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

## License

ISC
