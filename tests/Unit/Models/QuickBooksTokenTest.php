<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests\Unit\Models;

use E3DevelopmentSolutions\QuickBooks\Models\QuickBooksToken;
use E3DevelopmentSolutions\QuickBooks\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuickBooksTokenTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_token()
    {
        // Arrange
        $tokenData = [
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'realm_id' => '1234567890',
            'expires_in' => 3600,
            'refresh_token_expires_in' => 8726400,
            'token_type' => 'bearer',
        ];
        
        // Act
        $token = QuickBooksToken::create($tokenData);
        
        // Assert
        $this->assertDatabaseHas('quickbooks_tokens', [
            'id' => $token->id,
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'realm_id' => '1234567890',
        ]);
    }
    
    /** @test */
    public function it_encrypts_sensitive_fields()
    {
        // Arrange
        $token = QuickBooksToken::create([
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'realm_id' => '1234567890',
            'expires_in' => 3600,
            'refresh_token_expires_in' => 8726400,
            'token_type' => 'bearer',
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
        // Arrange
        $token = QuickBooksToken::create([
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'realm_id' => '1234567890',
            'expires_in' => 3600,
            'refresh_token_expires_in' => 8726400,
            'token_type' => 'bearer',
        ]);
        
        // Act
        $foundToken = QuickBooksToken::findByRealmId('1234567890');
        
        // Assert
        $this->assertNotNull($foundToken);
        $this->assertEquals($token->id, $foundToken->id);
    }
}
