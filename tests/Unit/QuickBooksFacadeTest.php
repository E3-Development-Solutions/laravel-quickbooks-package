<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests\Unit;

use E3DevelopmentSolutions\QuickBooks\Facades\QuickBooks as QuickBooksFacade;
use E3DevelopmentSolutions\QuickBooks\QuickBooks;
use E3DevelopmentSolutions\QuickBooks\Tests\TestCase;
use QuickBooksOnline\API\DataService\DataService;

class QuickBooksFacadeTest extends TestCase
{
    /** @test */
    public function it_resolves_the_facade()
    {
        // Act
        $quickbooks = QuickBooksFacade::getFacadeRoot();
        
        // Assert
        $this->assertInstanceOf(QuickBooks::class, $quickbooks);
    }
    
    /** @test */
    public function it_proxies_method_calls_to_service()
    {
        // Skip this test for now
        $this->markTestSkipped('Need to implement proper mocking for the QuickBooks facade');
        
        // Arrange
        $mockCustomer = new \stdClass();
        $mockCustomer->Id = '123';
        $mockCustomer->DisplayName = 'Test Customer';
        
        // Mock the FindById method on the DataService
        $this->dataService->shouldReceive('FindById')
            ->once()
            ->with('customer', '123')
            ->andReturn($mockCustomer);
            
        $this->dataService->shouldReceive('getLastError')
            ->andReturn(false);
            
        // Act
        $result = QuickBooksFacade::getCustomer('123');
        
        // Assert
        $this->assertEquals('123', $result->Id);
        $this->assertEquals('Test Customer', $result->DisplayName);
    }
}
