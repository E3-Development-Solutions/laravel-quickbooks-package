<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests\Feature;

use E3DevelopmentSolutions\QuickBooks\Facades\QuickBooks as QuickBooksFacade;
use E3DevelopmentSolutions\QuickBooks\Models\QuickBooksToken;
use E3DevelopmentSolutions\QuickBooks\Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;

class QuickBooksTokenTest extends TestCase
{
    use DatabaseMigrations, WithFaker;
    
    /**
     * Helper method to create a test user
     */
    protected function createTestUser()
    {
        $userModel = config('auth.providers.users.model');
        return $userModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }
    
    /** @test */
    public function it_can_store_and_retrieve_token()
    {
        $this->markTestSkipped('Needs implementation of processCallback method');
        
        // Create a test user
        $user = $this->createTestUser();
        Auth::login($user);
        
        // Set up the mock response for OAuth2LoginHelper
        $this->oauth2LoginHelper->shouldReceive('exchangeAuthorizationCodeForToken')
            ->once()
            ->with('test_auth_code', 'test_realm_id')
            ->andReturn([
                'refresh_token' => 'test_refresh_token',
                'access_token' => 'test_access_token',
                'expires_in' => 3600,
                'x_refresh_token_expires_in' => 8726400
            ]);
            
        // Call the service to process the callback
        $result = $this->app->make('quickbooks')->processCallback('test_auth_code', 'test_realm_id');
        
        // Assert the result is successful
        $this->assertTrue($result);
        
        // Verify the token was stored in the database
        $this->assertDatabaseHas('quickbooks_tokens', [
            'realm_id' => 'test_realm_id',
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
        ]);
    }

    /** @test */
    public function it_can_refresh_token()
    {
        $this->markTestSkipped('Needs implementation of refreshToken method');
        
        // Create a test user
        $user = $this->createTestUser();
        Auth::login($user);
        
        // Create a token in the database
        QuickBooksToken::create([
            'user_id' => $user->id,
            'access_token' => 'old_token',
            'refresh_token' => 'test_refresh',
            'realm_id' => 'test_realm_id',
            'expires_at' => now()->subDay(), // Expired token
            'refresh_token_expires_at' => now()->addMonth(),
        ]);
        
        // Mock the refresh token response
        $this->oauth2LoginHelper->shouldReceive('refreshToken')
            ->once()
            ->andReturn([
                'refresh_token' => 'new_refresh_token',
                'access_token' => 'new_access_token',
                'expires_in' => 3600,
            ]);
        
        // Call the refresh token method
        $refreshed = QuickBooksFacade::refreshToken();
        
        // Assert the token was refreshed
        $this->assertTrue($refreshed);
        
        // Verify the token was updated in the database
        $this->assertDatabaseHas('quickbooks_tokens', [
            'realm_id' => 'test_realm_id',
            'access_token' => 'new_access_token',
            'refresh_token' => 'new_refresh_token',
        ]);
    }
}
