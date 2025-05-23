<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests\Unit\Models;

use E3DevelopmentSolutions\QuickBooks\Models\QuickBooksToken;
use E3DevelopmentSolutions\QuickBooks\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuickBooksTokenTest extends TestCase
{
    use RefreshDatabase;

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
    public function it_can_create_a_token()
    {
        // Create a test user
        $user = $this->createTestUser();
        
        // Arrange
        $tokenData = [
            'user_id' => $user->id,
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'realm_id' => '1234567890',
            'expires_at' => now()->addHour(),
            'refresh_token_expires_at' => now()->addDays(30),
        ];
        
        // Act
        $token = QuickBooksToken::create($tokenData);
        
        // Assert - only check non-encrypted fields
        $this->assertDatabaseHas('quickbooks_tokens', [
            'id' => $token->id,
            'realm_id' => '1234567890',
        ]);
        
        // Verify the token was created with the correct values
        $this->assertEquals('test_access_token', $token->access_token);
        $this->assertEquals('test_refresh_token', $token->refresh_token);
    }
    
    /** @test */
    public function it_encrypts_sensitive_fields()
    {
        // Create a test user
        $user = $this->createTestUser();
        
        // Arrange
        $token = QuickBooksToken::create([
            'user_id' => $user->id,
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'realm_id' => '1234567890',
            'expires_at' => now()->addHour(),
            'refresh_token_expires_at' => now()->addDays(30),
        ]);        
        
        // Refresh the model to ensure we're getting it from the database
        $storedToken = QuickBooksToken::find($token->id);
        
        // Assert
        $this->assertNotEquals('test_access_token', $storedToken->getRawOriginal('access_token'));
        $this->assertNotEquals('test_refresh_token', $storedToken->getRawOriginal('refresh_token'));
        $this->assertEquals('test_access_token', $storedToken->access_token);
        $this->assertEquals('test_refresh_token', $storedToken->refresh_token);
    }
    
    /** @test */
    public function it_can_find_token_by_realm_id()
    {
        // Create a test user
        $user = $this->createTestUser();
        
        // Arrange
        $token = QuickBooksToken::create([
            'user_id' => $user->id,
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'realm_id' => '1234567890',
            'expires_at' => now()->addHour(),
            'refresh_token_expires_at' => now()->addDays(30),
        ]);
        
        // Act
        $foundToken = QuickBooksToken::findByRealmId('1234567890');
        
        // Assert
        $this->assertNotNull($foundToken);
        $this->assertEquals($token->id, $foundToken->id);
    }
}
