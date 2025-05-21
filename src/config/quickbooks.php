<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QuickBooks Authentication
    |--------------------------------------------------------------------------
    |
    | This section contains the authentication settings for QuickBooks Online.
    |
    */
    'auth_mode' => env('QUICKBOOKS_AUTH_MODE', 'oauth2'),
    'client_id' => env('QUICKBOOKS_CLIENT_ID'),
    'client_secret' => env('QUICKBOOKS_CLIENT_SECRET'),
    'redirect_uri' => env('QUICKBOOKS_REDIRECT_URI'),
    'scope' => env('QUICKBOOKS_SCOPE', 'com.intuit.quickbooks.accounting'),
    'base_url' => env('QUICKBOOKS_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | QuickBooks Entity Settings
    |--------------------------------------------------------------------------
    |
    | This section contains settings for QuickBooks entities.
    |
    */
    'entities' => [
        'customer' => [
            'enabled' => true,
            'model' => E3DevelopmentSolutions\QuickBooks\Models\Customer::class,
            'service' => E3DevelopmentSolutions\QuickBooks\Services\CustomerService::class,
        ],
        'invoice' => [
            'enabled' => true,
            'model' => E3DevelopmentSolutions\QuickBooks\Models\Invoice::class,
            'service' => E3DevelopmentSolutions\QuickBooks\Services\InvoiceService::class,
        ],
        'item' => [
            'enabled' => true,
            'model' => E3DevelopmentSolutions\QuickBooks\Models\Item::class,
            'service' => E3DevelopmentSolutions\QuickBooks\Services\ItemService::class,
        ],
        'purchase_order' => [
            'enabled' => true,
            'model' => E3DevelopmentSolutions\QuickBooks\Models\PurchaseOrder::class,
            'service' => E3DevelopmentSolutions\QuickBooks\Services\PurchaseOrderService::class,
        ],
        'sales_order' => [
            'enabled' => true,
            'model' => E3DevelopmentSolutions\QuickBooks\Models\SalesOrder::class,
            'service' => E3DevelopmentSolutions\QuickBooks\Services\SalesOrderService::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | QuickBooks User Integration
    |--------------------------------------------------------------------------
    |
    | This section contains settings for QuickBooks user integration.
    |
    */
    'user' => [
        'model' => App\Models\User::class,
        'table' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | QuickBooks Filament Integration
    |--------------------------------------------------------------------------
    |
    | This section contains settings for QuickBooks Filament integration.
    |
    */
    'filament' => [
        'enabled' => true,
        'navigation_group' => 'QuickBooks',
        'resources' => [
            'customer' => true,
            'invoice' => true,
            'item' => true,
            'purchase_order' => true,
            'sales_order' => true,
        ],
    ],
];
