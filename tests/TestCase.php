<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests;

use E3DevelopmentSolutions\QuickBooks\QuickBooksServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as TestBenchTestCase;

class TestCase extends TestBenchTestCase
{
    use RefreshDatabase;

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app)
    {
        return [
            QuickBooksServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app)
    {
        return [
            'QuickBooks' => \E3DevelopmentSolutions\QuickBooks\Facades\QuickBooks::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup QuickBooks configuration with test values
        $app['config']->set('quickbooks', [
            'auth_mode' => 'oauth2',
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'http://localhost/quickbooks/callback',
            'scope' => 'com.intuit.quickbooks.accounting',
            'base_url' => 'development',
        ]);
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        
        // Run package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        // Run migrations
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
        
        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');
        });
    }
}
