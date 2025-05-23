<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests\Feature;

use E3DevelopmentSolutions\QuickBooks\Facades\QuickBooks as QuickBooksFacade;
use E3DevelopmentSolutions\QuickBooks\QuickBooks;
use E3DevelopmentSolutions\QuickBooks\Tests\TestCase;
use QuickBooksOnline\API\DataService\DataService;
use Mockery;

class QuickBooksBasicTest extends TestCase
{
   // In QuickBooksBasicTest.php
   protected function setUp(): void
   {
       parent::setUp();
       
       // Mock the DataService
       $this->dataService = Mockery::mock(DataService::class);
       
       // Create and bind the QuickBooks instance
       $this->app->instance('quickbooks', new QuickBooks($this->dataService));
       
       // Manually register the facade alias
       $this->app->alias('quickbooks', QuickBooks::class);
   }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_resolve_quickbooks_facade()
    {
        $quickbooks = $this->app->make('quickbooks');
        $this->assertInstanceOf(QuickBooks::class, $quickbooks);
    }

    /** @test */
    public function it_can_get_datacenter_url()
    {
        // Set up the expectation
        $this->dataService->shouldReceive('getServiceURL')
            ->once()
            ->andReturn('https://sandbox-quickbooks.api.intuit.com');
        
        // Test the facade
        $this->assertEquals(
            'https://sandbox-quickbooks.api.intuit.com',
            QuickBooksFacade::getDataServiceBaseUrl()
        );
    }
}
