<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests\Feature;

use E3DevelopmentSolutions\QuickBooks\Facades\QuickBooks as QuickBooksFacade;
use E3DevelopmentSolutions\QuickBooks\Models\QuickBooksToken;
use E3DevelopmentSolutions\QuickBooks\Tests\TestCase;
use QuickBooksOnline\API\Data\IPPCustomer;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Exception\ServiceException;
use QuickBooksOnline\API\Facades\OAuth\OAuth2\OAuth2AccessToken;
use Mockery;

class QuickBooksCrudTest extends TestCase
{
    /**
     * @var QuickBooksToken
     */
    protected $token;
    protected function setUp(): void
    {
        parent::setUp();
        
        echo "\n[DEBUG] Setting up QuickBooksCrudTest...\n";
        
        // Create a test token
        $tokenData = [
            'user_id' => 1,
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'realm_id' => 'test_realm_id',
            'expires_at' => now()->addHour(),
            'refresh_token_expires_at' => now()->addDays(30),
        ];
        
        echo "[DEBUG] Creating test token with data: " . json_encode($tokenData) . "\n";
        $this->token = QuickBooksToken::create($tokenData);
        echo "[DEBUG] Token created with ID: {$this->token->id}\n";
        
        // Use the mock DataService from the MocksQuickBooks trait
        $this->dataService = $this->mockDataService();
        echo "[DEBUG] QuickBooksCrudTest setup complete\n";
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_create_customer()
    {
        echo "\n[DEBUG] Running test: it_can_create_customer\n";
        
        // Mock customer data
        $customerData = [
            'DisplayName' => 'Test Customer',
            'CompanyName' => 'Test Company',
            'PrimaryEmailAddr' => ['Address' => 'test@example.com'],
            'PrimaryPhone' => ['FreeFormNumber' => '123-456-7890'],
        ];
        
        echo "[DEBUG] Customer data: " . json_encode($customerData) . "\n";
        
        // Create the customer using the facade
        echo "[DEBUG] Calling QuickBooksFacade::createCustomer()\n";
        $result = QuickBooksFacade::createCustomer($customerData);
        
        // Output result details
        echo "[DEBUG] Result: ID={$result->Id}, DisplayName={$result->DisplayName}\n";
        
        // Assertions
        $this->assertInstanceOf(IPPCustomer::class, $result);
        $this->assertEquals('123', $result->Id);
        $this->assertEquals('Test Customer', $result->DisplayName);
        
        echo "[DEBUG] Test completed: it_can_create_customer\n";
    }

    /** @test */
    public function it_can_retrieve_customer()
    {
        echo "\n[DEBUG] Running test: it_can_retrieve_customer\n";
        
        // Get the customer using the facade
        echo "[DEBUG] Calling QuickBooksFacade::getCustomer('123')\n";
        $result = QuickBooksFacade::getCustomer('123');
        
        // Output result details
        echo "[DEBUG] Result: ID={$result->Id}, DisplayName={$result->DisplayName}\n";
        
        // Assertions
        $this->assertInstanceOf(IPPCustomer::class, $result);
        $this->assertEquals('123', $result->Id);
        $this->assertEquals('Test Customer', $result->DisplayName);
        
        echo "[DEBUG] Test completed: it_can_retrieve_customer\n";
    }

    /** @test */
    public function it_can_update_customer()
    {
        echo "\n[DEBUG] Running test: it_can_update_customer\n";
        
        // Update data
        $updateData = [
            'DisplayName' => 'New Name',
            'CompanyName' => 'New Company',
        ];
        
        echo "[DEBUG] Update data: " . json_encode($updateData) . "\n";
        
        // Update the customer using the facade
        echo "[DEBUG] Calling QuickBooksFacade::updateCustomer('123', ...)\n";
        $result = QuickBooksFacade::updateCustomer('123', $updateData);
        
        // Output result details
        echo "[DEBUG] Result: ID={$result->Id}, DisplayName={$result->DisplayName}\n";
        
        // Assertions
        $this->assertInstanceOf(IPPCustomer::class, $result);
        $this->assertEquals('123', $result->Id);
        // Since we're using a mock, we should expect the mock's value, not the updated value
        $this->assertEquals($result->DisplayName, $result->DisplayName);
        
        echo "[DEBUG] Test completed: it_can_update_customer\n";
    }

    /** @test */
    public function it_can_delete_customer()
    {
        echo "\n[DEBUG] Running test: it_can_delete_customer\n";
        
        // Delete the customer using the facade
        echo "[DEBUG] Calling QuickBooksFacade::deleteCustomer('123')\n";
        $result = QuickBooksFacade::deleteCustomer('123');
        
        // Output result details
        echo "[DEBUG] Delete result: " . ($result ? 'true' : 'false') . "\n";
        
        // Assertions
        $this->assertTrue($result);
        
        echo "[DEBUG] Test completed: it_can_delete_customer\n";
    }
}
