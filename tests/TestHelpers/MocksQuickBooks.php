<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests\TestHelpers;

use Mockery;
use QuickBooksOnline\API\DataService\DataService;

/**
 * Trait for mocking QuickBooks DataService
 */
trait MocksQuickBooks
{
    /**
     * @var \Mockery\MockInterface|\QuickBooksOnline\API\DataService\DataService
     */
    protected $dataService;

    /**
     * Set up the DataService mock
     */
    protected function mockDataService()
    {
        $this->dataService = Mockery::mock(DataService::class);
        
        // Create a partial mock of QuickBooks that allows us to inject our mock DataService
        $quickBooks = new \E3DevelopmentSolutions\QuickBooks\QuickBooks($this->dataService);
        
        $this->app->instance('quickbooks', $quickBooks);
        $this->app->alias('quickbooks', \E3DevelopmentSolutions\QuickBooks\QuickBooks::class);
        
        return $quickBooks;
    }

    /**
     * Assert that a method was called on the DataService
     */
    protected function assertDataServiceMethodCalled(string $method, array $with = null, $return = null, $times = 1)
    {
        $expectation = $this->dataService->shouldReceive($method)->times($times);
        
        if ($with !== null) {
            $expectation->withArgs($with);
        }
        if ($return !== null) {
            $expectation->andReturn($return);
        }
    }

    /**
     * Assert that no errors occurred
     */
    protected function assertNoErrors()
    {
        $this->dataService->shouldReceive('getLastError')
            ->andReturn(false);
    }
}
