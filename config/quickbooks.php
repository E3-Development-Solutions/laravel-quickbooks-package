<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QuickBooks Online Settings
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for QuickBooks Online. You can
    | find these credentials in the QuickBooks Developer Dashboard.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Authentication Mode
    |--------------------------------------------------------------------------
    |
    | Supported: "oauth2"
    |
    */
    'auth_mode' => env('QUICKBOOKS_AUTH_MODE', 'oauth2'),

    /*
    |--------------------------------------------------------------------------
    | Client ID
    |--------------------------------------------------------------------------
    |
    | The client ID from your QuickBooks Developer Dashboard.
    |
    */
    'client_id' => env('QUICKBOOKS_CLIENT_ID'),


    /*
    |--------------------------------------------------------------------------
    | Client Secret
    |--------------------------------------------------------------------------
    |
    | The client secret from your QuickBooks Developer Dashboard.
    |
    */
    'client_secret' => env('QUICKBOOKS_CLIENT_SECRET'),


    /*
    |--------------------------------------------------------------------------
    | Redirect URI
    |--------------------------------------------------------------------------
    |
    | The redirect URI that is registered in your QuickBooks Developer Dashboard.
    |
    */
    'redirect_uri' => env('QUICKBOOKS_REDIRECT_URI', config('app.url').'/quickbooks/callback'),

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    |
    | The scope for the OAuth connection. Multiple scopes should be separated by spaces.
    | Example: 'com.intuit.quickbooks.accounting com.intuit.quickbooks.payment'
    |
    */
    'scope' => env('QUICKBOOKS_SCOPE', 'com.intuit.quickbooks.accounting'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the QuickBooks API.
    | Use 'development' for sandbox and 'production' for production.
    |
    */
    'base_url' => env('QUICKBOOKS_BASE_URL', 'development'),

    /*
    |--------------------------------------------------------------------------
    | Token Model
    |--------------------------------------------------------------------------
    |
    | The model used to store OAuth tokens.
    |
    */
    'token_model' => \E3DevelopmentSolutions\QuickBooks\Models\QuickBooksToken::class,
];
