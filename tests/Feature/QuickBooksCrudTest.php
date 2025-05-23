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
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test token
        $this->token = QuickBooksToken::create([
            'user_id' => 1,
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'realm_id' => 'test_realm_id',
            'expires_at' => now()->addHour(),
            'refresh_token_expires_at' => now()->addDays(30),
        ]);
        
        // Mock the DataService
        $this->dataService = Mockery::mock(DataService::class);
        $this->app->instance(DataService::class, $this->dataService);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_create_customer()
    {
        // Mock customer data
        $customerData = [
            'DisplayName' => 'Test Customer',
            'CompanyName' => 'Test Company',
            'PrimaryEmailAddr' => ['Address' => 'test@example.com'],
            'PrimaryPhone' => ['FreeFormNumber' => '123-456-7890'],
        ];
        
        // Mock the response
        $customer = new IPPCustomer();
        $customer->Id = '123';
        $customer->DisplayName = 'Test Customer';
        $customer->CompanyName = 'Test Company';
        
        // Expect the create call
        $this->dataService->shouldReceive('Add')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof IPPCustomer && 
                       $arg->DisplayName === 'Test Customer';
            }))
            ->andReturn($customer);
        
        // Create the customer
        $result = QuickBooksFacade::createCustomer($customerData);
        
        // Assertions
        $this->assertInstanceOf(IPPCustomer::class, $result);
        $this->assertEquals('123', $result->Id);
        $this->assertEquals('Test Customer', $result->DisplayName);
    }

    /** @test */
    public function it_can_retrieve_customer()
    {
        // Mock customer
        $customer = new IPPCustomer();
        $customer->Id = '123';
        $customer->DisplayName = 'Test Customer';
        $customer->CompanyName = 'Test Company';
        
        // Expect the query
        $this->dataService->shouldReceive('FindById')
            ->once()
            ->with('customer', '123')
            ->andReturn($customer);
        
        // Get the customer
        $result = QuickBooksFacade::getCustomer('123');
        
        // Assertions
        $this->assertInstanceOf(IPPCustomer::class, $result);
        $this->assertEquals('123', $result->Id);
        $this->assertEquals('Test Customer', $result->DisplayName);
    }

    /** @test */
    public function it_can_update_customer()
    {
        // Mock existing customer
        $existingCustomer = new IPPCustomer();
        $existingCustomer->Id = '123';
        $existingCustomer->DisplayName = 'Old Name';
        $existingCustomer->CompanyName = 'Old Company';
        
        // Mock updated customer
        $updatedCustomer = new IPPCustomer();
        $updatedCustomer->Id = '123';
        $updatedCustomer->DisplayName = 'New Name';
        $updatedCustomer->CompanyName = 'New Company';
        
        // Expect the find and update calls
        $this->dataService->shouldReceive('FindById')
            ->once()
            ->with('customer', '123')
            ->andReturn($existingCustomer);
            
        $this->dataService->shouldReceive('Update')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg->DisplayName === 'New Name';
            }))
            ->andReturn($updatedCustomer);
        
        // Update the customer
        $result = QuickBooksFacade::updateCustomer('123', [
            'DisplayName' => 'New Name',
            'CompanyName' => 'New Company',
        ]);
        
        // Assertions
        $this->assertInstanceOf(IPPCustomer::class, $result);
        $this->assertEquals('123', $result->Id);
        $this->assertEquals('New Name', $result->DisplayName);
    }

    /** @test */
    public function it_can_delete_customer()
    {
        // Mock customer
        $customer = new IPPCustomer();
        $customer->Id = '123';
        $customer->DisplayName = 'Test Customer';
        $customer->Active = true;
        
        // Mock the find and delete calls
        $this->dataService->shouldReceive('FindById')
            ->once()
            ->with('customer', '123')
            ->andReturn($customer);
            
        $this->dataService->shouldReceive('Delete')
            ->once()
            ->with($customer)
            ->andReturn(true);
        
        // Delete the customer
        $result = QuickBooksFacade::deleteCustomer('123');
        
        // Assertions
        $this->assertTrue($result);
    }
}
