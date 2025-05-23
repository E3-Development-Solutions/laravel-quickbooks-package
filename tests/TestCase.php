<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests;

use E3DevelopmentSolutions\QuickBooks\QuickBooksServiceProvider;
use E3DevelopmentSolutions\QuickBooks\Tests\TestHelpers\MocksQuickBooks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Mockery;
use Orchestra\Testbench\TestCase as TestBenchTestCase;

class TestCase extends TestBenchTestCase
{
    use RefreshDatabase, MocksQuickBooks;
    
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test configuration
        Config::set('quickbooks', [
            'client_id' => env('QUICKBOOKS_CLIENT_ID', 'test_client_id'),
            'client_secret' => env('QUICKBOOKS_CLIENT_SECRET', 'test_client_secret'),
            'redirect_uri' => env('QUICKBOOKS_REDIRECT_URI', 'http://localhost:8000/quickbooks/callback'),
            'scope' => env('QUICKBOOKS_SCOPE', 'com.intuit.quickbooks.accounting'),
            'base_url' => env('QUICKBOOKS_BASE_URL', 'development'),
            'auth_mode' => env('QUICKBOOKS_AUTH_MODE', 'oauth2'),
        ]);
        
        // Mock the DataService
        $this->mockDataService();
    }
    
    /**
     * Clean up the testing environment before the next test.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        
        if ($container = Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }
        
        Mockery::close();
    }

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
