<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests\Unit\Services;

use E3DevelopmentSolutions\QuickBooks\QuickBooks;
use E3DevelopmentSolutions\QuickBooks\Tests\TestCase;
use QuickBooksOnline\API\Data\IPPCustomer;
use QuickBooksOnline\API\Exception\ServiceException;

class QuickBooksServiceTest extends TestCase
{
    /** @test */
    public function it_can_create_a_customer()
    {
        // Arrange
        $customerData = [
            'DisplayName' => 'Test Customer',
            'GivenName' => 'Test',
            'FamilyName' => 'Customer',
            'PrimaryEmailAddr' => ['Address' => 'test@example.com'],
        ];
        
        $expectedCustomer = new IPPCustomer();
        $expectedCustomer->Id = '123';
        
        // Expect the Add method to be called with a customer that matches our data
        $this->dataService->shouldReceive('Add')
            ->once()
            ->withArgs(function($customer) use ($customerData) {
                return $customer->DisplayName === $customerData['DisplayName']
                    && $customer->GivenName === $customerData['GivenName']
                    && $customer->FamilyName === $customerData['FamilyName']
                    && $customer->PrimaryEmailAddr->Address === $customerData['PrimaryEmailAddr']['Address'];
            })
            ->andReturn(['Id' => '123']);
            
        $this->assertNoErrors();
        
        // Act
        $result = $this->app->make('quickbooks')->createCustomer($customerData);
        
        // Assert
        $this->assertEquals('123', $result->Id);
    }
    
    /** @test */
    public function it_can_retrieve_a_customer()
    {
        // Arrange
        $customerId = '123';
        $expectedCustomer = new IPPCustomer();
        $expectedCustomer->Id = $customerId;
        $expectedCustomer->DisplayName = 'Test Customer';
        
        $this->dataService->shouldReceive('FindById')
            ->once()
            ->with('customer', $customerId)
            ->andReturn($expectedCustomer);
            
        $this->assertNoErrors();
        
        // Act
        $result = $this->app->make('quickbooks')->getCustomer($customerId);
        
        // Assert
        $this->assertEquals($customerId, $result->Id);
        $this->assertEquals('Test Customer', $result->DisplayName);
    }
    
    /** @test */
    public function it_handles_errors_when_creating_customer()
    {
        // Arrange
        $customerData = [
            'DisplayName' => 'Test Customer',
        ];
        
        $error = new ServiceException('Error creating customer', 400);
        
        $this->dataService->shouldReceive('Add')
            ->once()
            ->andThrow($error);
            
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Error creating customer');
        
        // Act
        $this->app->make('quickbooks')->createCustomer($customerData);
    }
}
