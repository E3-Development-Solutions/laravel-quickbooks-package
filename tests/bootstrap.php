<?php

use Illuminate\Contracts\Console\Kernel;

$app = require __DIR__.'/../vendor/orchestra/testbench-core/laravel/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

// Set the application key
$app['config']->set('app.key', 'base64:'.base64_encode(
    'base64:'.base64_encode(random_bytes(32))
));

// Set the database configuration
$app['config']->set('database.default', 'sqlite');
$app['config']->set('database.connections.sqlite', [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);

// Load package service providers
$app->register(\E3DevelopmentSolutions\QuickBooks\QuickBooksServiceProvider::class);

return $app;
