<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests\Feature;

use E3DevelopmentSolutions\QuickBooks\Models\QuickBooksToken;
use E3DevelopmentSolutions\QuickBooks\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Mockery;

class AuthenticationTest extends TestCase
{
    /** @test */
    public function it_can_generate_authorization_url()
    {
        // Arrange
        $expectedUrl = 'https://appcenter.intuit.com/connect/oauth2?' . http_build_query([
            'client_id' => 'test_client_id',
            'redirect_uri' => 'http://localhost:8000/quickbooks/callback',
            'response_type' => 'code',
            'scope' => 'com.intuit.quickbooks.accounting',
            'state' => csrf_token(),
        ]);
        
        // Act
        $url = $this->app->make('quickbooks')->getAuthorizationUrl();
        
        // Assert
        $this->assertStringStartsWith('https://appcenter.intuit.com/connect/oauth2?', $url);
        $this->assertStringContainsString('client_id=test_client_id', $url);
        $this->assertStringContainsString('scope=com.intuit.quickbooks.accounting', $url);
    }
    
    /** @test */
    public function it_can_handle_oauth_callback()
    {
        // Skip if we don't have the required environment variables
        if (!getenv('QUICKBOOKS_CLIENT_ID') || !getenv('QUICKBOOKS_CLIENT_SECRET')) {
            $this->markTestSkipped('QUICKBOOKS_CLIENT_ID and QUICKBOOKS_CLIENT_SECRET environment variables are required');
        }
        
        // This is a simplified test that would need to be adjusted based on your implementation
        $this->markTestIncomplete('Need to implement OAuth callback test');
    }
    
    /** @test */
    public function it_can_refresh_an_access_token()
    {
        // Skip if we don't have the required environment variables
        if (!getenv('QUICKBOOKS_REFRESH_TOKEN')) {
            $this->markTestSkipped('QUICKBOOKS_REFRESH_TOKEN environment variable is required');
        }
        
        // This is a simplified test that would need to be adjusted based on your implementation
        $this->markTestIncomplete('Need to implement token refresh test');
    }
}
