<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests\Feature;

use E3DevelopmentSolutions\QuickBooks\Facades\QuickBooks as QuickBooksFacade;
use E3DevelopmentSolutions\QuickBooks\Models\QuickBooksToken;
use E3DevelopmentSolutions\QuickBooks\Tests\TestCase;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use Mockery;

class QuickBooksTokenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock OAuth2LoginHelper
        $this->oauthHelper = Mockery::mock(OAuth2LoginHelper::class);
        $this->app->instance(OAuth2LoginHelper::class, $this->oauthHelper);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_store_and_retrieve_token()
    {
        // Create a mock token
        $token = new OAuth2AccessToken(
            'test_token',
            'test_refresh',
            3600,
            time() + 3600,
            'test_realm_id'
        );

        // Mock the token exchange
        $this->oauthHelper->shouldReceive('exchangeAuthorizationCodeForToken')
            ->once()
            ->with('test_auth_code', 'test_realm_id')
            ->andReturn($token);

        // Call the method that exchanges auth code for token
        $storedToken = QuickBooksFacade::exchangeAuthorizationCodeForToken('test_auth_code', 'test_realm_id');

        // Verify the token was stored in the database
        $this->assertDatabaseHas('quickbooks_tokens', [
            'realm_id' => 'test_realm_id',
            'access_token' => 'test_token',
            'refresh_token' => 'test_refresh',
        ]);

        // Verify we can retrieve the token
        $retrievedToken = QuickBooksToken::where('realm_id', 'test_realm_id')->first();
        $this->assertNotNull($retrievedToken);
        $this->assertEquals('test_token', $retrievedToken->access_token);
    }

    /** @test */
    public function it_can_refresh_token()
    {
        // Create an existing token in the database
        $existingToken = QuickBooksToken::create([
            'user_id' => 1,
            'access_token' => 'old_token',
            'refresh_token' => 'test_refresh',
            'realm_id' => 'test_realm_id',
            'expires_at' => now()->subDay(),
            'refresh_token_expires_at' => now()->addDays(30),
        ]);

        // Create a new token for the refresh
        $newToken = new OAuth2AccessToken(
            'new_access_token',
            'new_refresh_token',
            3600,
            time() + 3600,
            'test_realm_id'
        );

        // Mock the refresh token call
        $this->oauthHelper->shouldReceive('refreshAccessTokenWithRefreshToken')
            ->once()
            ->with('test_refresh')
            ->andReturn($newToken);

        // Call the refresh method
        $refreshed = QuickBooksFacade::refreshToken($existingToken);

        // Verify the token was updated in the database
        $this->assertDatabaseHas('quickbooks_tokens', [
            'id' => $existingToken->id,
            'access_token' => 'new_access_token',
            'refresh_token' => 'new_refresh_token',
        ]);
    }
}
