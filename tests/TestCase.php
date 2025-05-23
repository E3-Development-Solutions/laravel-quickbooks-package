<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests;

use E3DevelopmentSolutions\QuickBooks\QuickBooksServiceProvider;
use E3DevelopmentSolutions\QuickBooks\Tests\TestHelpers\MocksQuickBooks;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Orchestra\Testbench\TestCase as TestBenchTestCase;

class TestCase extends TestBenchTestCase
{
    use MocksQuickBooks;
    
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create database tables if they don't exist
        if (!Schema::hasTable('users')) {
            Schema::create('users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
                
                // QuickBooks fields
                $table->text('qb_access_token')->nullable();
                $table->text('qb_refresh_token')->nullable();
                $table->timestamp('qb_token_expires')->nullable();
                $table->string('qb_realm_id')->nullable();
            });
        }
        
        if (!Schema::hasTable('quickbooks_tokens')) {
            Schema::create('quickbooks_tokens', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->text('access_token');
                $table->text('refresh_token');
                $table->string('realm_id');
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('refresh_token_expires_at')->nullable();
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users');
            });
        }
        
        // Set up test configuration
        Config::set('quickbooks', [
            'client_id' => env('QUICKBOOKS_CLIENT_ID', 'test_client_id'),
            'client_secret' => env('QUICKBOOKS_CLIENT_SECRET', 'test_client_secret'),
            'redirect_uri' => env('QUICKBOOKS_REDIRECT_URI', 'http://localhost:8000/quickbooks/callback'),
            'scope' => env('QUICKBOOKS_SCOPE', 'com.intuit.quickbooks.accounting'),
            'base_url' => env('QUICKBOOKS_BASE_URL', 'development'),
            'auth_mode' => env('QUICKBOOKS_AUTH_MODE', 'oauth2'),
            'user_id' => env('QUICKBOOKS_USER_ID', 'test_user_id'),
            'realm_id' => env('QUICKBOOKS_REALM_ID', 'test_realm_id'),
        ]);
        
        // Mock the DataService
        $this->mockDataService();
    }
    
    /**
     * Clean up the testing environment before the next test.
     */
    protected function tearDown(): void
    {
        Schema::dropIfExists('quickbooks_tokens');
        Schema::dropIfExists('users');
        
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
        
        // Set up the test user model for authentication
        $app['config']->set('auth.providers.users.model', \E3DevelopmentSolutions\QuickBooks\Tests\TestHelpers\TestUser::class);
        
        // Set up encryption key for testing
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        
        // Set up QuickBooks configuration
        $app['config']->set('quickbooks.user_id', env('QUICKBOOKS_USER_ID', 'test_user_id'));
        $app['config']->set('quickbooks.realm_id', env('QUICKBOOKS_REALM_ID', 'test_realm_id'));
    }
}
