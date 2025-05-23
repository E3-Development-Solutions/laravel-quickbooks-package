# Laravel QuickBooks Integration

[![Latest Version on Packagist](https://img.shields.io/packagist/v/e3-development-solutions/quickbooks.svg?style=flat-square)](https://packagist.org/packages/e3-development-solutions/quickbooks)
[![Total Downloads](https://img.shields.io/packagist/dt/e3-development-solutions/quickbooks.svg?style=flat-square)](https://packagist.org/packages/e3-development-solutions/quickbooks)

A Laravel package for integrating QuickBooks Online with your Laravel application, featuring Filament admin panel support.

## Features

- OAuth2 authentication with QuickBooks Online
- Integration with Laravel's authentication system
- Support for Customer, Invoice, Item, Purchase Order, and Sales Order entities
- Filament admin panel integration
- Comprehensive exception handling
- Configurable settings

## Requirements

- PHP 8.1 or higher
- Laravel 10.x, 11.x, or 12.x
- Filament 3.x
- QuickBooks Online account

## Installation

You can install the package via composer:

```bash
composer require e3-development-solutions/quickbooks
```

After installing the package, publish the configuration file:

```bash
php artisan vendor:publish --provider="E3DevelopmentSolutions\QuickBooks\QuickBooksServiceProvider" --tag="config"
```

Publish and run the migrations:

```bash
php artisan vendor:publish --provider="E3DevelopmentSolutions\QuickBooks\QuickBooksServiceProvider" --tag="migrations"
php artisan migrate
```

## Testing

To run the test suite, you'll need to install the development dependencies:

```bash
composer require --dev orchestra/testbench mockery/mockery
```

Then run the tests with:

```bash
composer test
```

### Test Environment

Create a `.env.testing` file with the following variables:

```
APP_ENV=testing
APP_DEBUG=true
APP_KEY=base64:testkey123456789012345678901234567890=

DB_CONNECTION=sqlite
DB_DATABASE=:memory:

CACHE_DRIVER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array

QUICKBOOKS_CLIENT_ID=test_client_id
QUICKBOOKS_CLIENT_SECRET=test_client_secret
QUICKBOOKS_REDIRECT_URI=http://localhost:8000/quickbooks/callback
QUICKBOOKS_SCOPE=com.intuit.quickbooks.accounting
```

### Writing Tests

When writing tests, you can use the `MocksQuickBooks` trait to easily mock the QuickBooks DataService:

```php
use E3DevelopmentSolutions\QuickBooks\Tests\TestHelpers\MocksQuickBooks;

class YourTest extends TestCase
{
    use MocksQuickBooks;
    
    public function test_something()
    {
        // Set up expectations
        $this->dataService->shouldReceive('someMethod')
            ->once()
            ->andReturn('expected result');
            
        // Your test code here
    }
}
```

## Configuration

### Environment Variables

Add the following variables to your `.env` file:

```
QUICKBOOKS_AUTH_MODE=oauth2
QUICKBOOKS_CLIENT_ID=your-client-id
QUICKBOOKS_CLIENT_SECRET=your-client-secret
QUICKBOOKS_REDIRECT_URI=https://your-app.com/quickbooks/callback
QUICKBOOKS_SCOPE=com.intuit.quickbooks.accounting
QUICKBOOKS_BASE_URL=
```

For development, QuickBooks uses a sandbox environment. The base URL will be automatically set based on your application environment:
- Production: `https://quickbooks.api.intuit.com/`
- Development/Sandbox: `https://sandbox-quickbooks.api.intuit.com/`

### QuickBooks Developer Account

1. Sign up for a [QuickBooks Developer account](https://developer.intuit.com/)
2. Create a new app in the developer dashboard
3. Set the redirect URI to match your `QUICKBOOKS_REDIRECT_URI` environment variable
4. Copy the Client ID and Client Secret to your `.env` file

## User Model Integration

### Using the Trait

Add the `HasQuickBooksAuthentication` trait to your User model:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use E3DevelopmentSolutions\QuickBooks\Services\Traits\HasQuickBooksAuthentication;

class User extends Authenticatable
{
    use HasQuickBooksAuthentication;
    
    // ...
}
```

### Database Fields

The package adds the following fields to your users table:

- `qb_access_token` - The QuickBooks access token
- `qb_refresh_token` - The QuickBooks refresh token
- `qb_token_expires` - When the access token expires
- `qb_realm_id` - The QuickBooks company ID

## Basic Usage

### Authentication

#### Connect to QuickBooks

To connect a user to QuickBooks, redirect them to the connect route:

```php
return redirect()->route('quickbooks.connect');
```

This will redirect the user to the QuickBooks authorization page. After authorizing, they will be redirected back to your application.

#### Check Connection Status

You can check if a user is connected to QuickBooks:

```php
if (auth()->user()->isConnectedToQuickBooks()) {
    // User is connected
}
```

#### Disconnect from QuickBooks

To disconnect a user from QuickBooks:

```php
auth()->user()->disconnectFromQuickBooks();
```

### Using the QuickBooks Facade

The package provides a `QuickBooks` facade for easy access to the QuickBooks API:

```php
use E3DevelopmentSolutions\QuickBooks\Facades\QuickBooks;

// Get the QuickBooks data service
$dataService = QuickBooks::getDataService();

// Use the data service to interact with QuickBooks
$customers = $dataService->Query("SELECT * FROM Customer");
```

### Middleware

The package includes a middleware to ensure users are connected to QuickBooks:

```php
Route::middleware(['auth', 'quickbooks.authenticated'])->group(function () {
    // Routes that require QuickBooks authentication
});
```

## Filament Integration

### Dashboard Connection Button

The package provides a Filament page for connecting to QuickBooks. Add it to your Filament panel:

```php
use E3DevelopmentSolutions\QuickBooks\Filament\Pages\Auth\Connect;

public function panel(Panel $panel): Panel
{
    return $panel
        ->pages([
            // Your other pages...
            Connect::class,
        ]);
}
```

### Entity Resources

The package includes Filament resources for QuickBooks entities. To use them, add them to your Filament panel:

```php
use E3DevelopmentSolutions\QuickBooks\Filament\Resources\CustomerResource;
use E3DevelopmentSolutions\QuickBooks\Filament\Resources\InvoiceResource;
use E3DevelopmentSolutions\QuickBooks\Filament\Resources\ItemResource;
use E3DevelopmentSolutions\QuickBooks\Filament\Resources\PurchaseOrderResource;
use E3DevelopmentSolutions\QuickBooks\Filament\Resources\SalesOrderResource;

public function panel(Panel $panel): Panel
{
    return $panel
        ->resources([
            // Your other resources...
            CustomerResource::class,
            InvoiceResource::class,
            ItemResource::class,
            PurchaseOrderResource::class,
            SalesOrderResource::class,
        ]);
}
```

## Working with QuickBooks Entities

### Customers

```php
use E3DevelopmentSolutions\QuickBooks\Services\CustomerService;

// Inject the service
public function __construct(protected CustomerService $customerService) {}

// Get all customers
$customers = $this->customerService->all();

// Find a customer by ID
$customer = $this->customerService->find($id);

// Create a customer
$customer = $this->customerService->create([
    'DisplayName' => 'John Doe',
    'PrimaryEmailAddr' => [
        'Address' => 'john.doe@example.com',
    ],
]);

// Update a customer
$customer = $this->customerService->update($id, [
    'DisplayName' => 'John Doe Updated',
]);

// Delete a customer
$this->customerService->delete($id);
```

### Invoices

```php
use E3DevelopmentSolutions\QuickBooks\Services\InvoiceService;

// Inject the service
public function __construct(protected InvoiceService $invoiceService) {}

// Get all invoices
$invoices = $this->invoiceService->all();

// Find an invoice by ID
$invoice = $this->invoiceService->find($id);

// Create an invoice
$invoice = $this->invoiceService->create([
    'CustomerRef' => [
        'value' => $customerId,
    ],
    'Line' => [
        [
            'Amount' => 100.00,
            'DetailType' => 'SalesItemLineDetail',
            'SalesItemLineDetail' => [
                'ItemRef' => [
                    'value' => $itemId,
                ],
            ],
        ],
    ],
]);

// Update an invoice
$invoice = $this->invoiceService->update($id, [
    'CustomerRef' => [
        'value' => $newCustomerId,
    ],
]);

// Delete an invoice
$this->invoiceService->delete($id);
```

Similar patterns apply for Item, Purchase Order, and Sales Order entities.

## Error Handling

The package provides custom exceptions for handling QuickBooks-related errors:

- `QuickBooksException` - Base exception for all QuickBooks errors
- `QuickBooksAuthException` - Authentication-related errors
- `QuickBooksEntityException` - Entity-related errors

Example:

```php
use E3DevelopmentSolutions\QuickBooks\Exceptions\QuickBooksAuthException;
use E3DevelopmentSolutions\QuickBooks\Exceptions\QuickBooksEntityException;

try {
    $customer = $customerService->find($id);
} catch (QuickBooksEntityException $e) {
    // Handle entity error
    return back()->with('error', $e->getMessage());
} catch (QuickBooksAuthException $e) {
    // Handle authentication error
    return redirect()->route('quickbooks.connect')
        ->with('error', 'Please reconnect to QuickBooks: ' . $e->getMessage());
}
```

## Advanced Configuration

### Custom Entity Mapping

You can customize the entity mapping in the `config/quickbooks.php` file:

```php
'entities' => [
    'customer' => [
        'enabled' => true,
        'model' => App\Models\Customer::class, // Your custom model
        'service' => App\Services\CustomCustomerService::class, // Your custom service
    ],
    // ...
],
```

### Custom User Model

You can specify a custom user model in the configuration:

```php
'user' => [
    'model' => App\Models\Admin::class, // Your custom user model
    'table' => 'admins', // Your custom user table
],
```

## Testing

```bash
composer test
```

## Security

If you discover any security-related issues, please email security@example.com instead of using the issue tracker.

## Credits

- [E3 Development Solutions](https://github.com/e3-development-solutions)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
