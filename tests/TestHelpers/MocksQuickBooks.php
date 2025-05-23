<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests\TestHelpers;

use Mockery;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Data\IPPCustomer;
use QuickBooksOnline\API\Data\IPPEmailAddress;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Exception\ServiceException;

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
     * @var \Mockery\MockInterface|\QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper
     */
    protected $oauth2LoginHelper;

    /**
     * Set up the DataService mock
     */
    protected function mockDataService()
    {
        echo "\n[DEBUG] Setting up QuickBooks DataService mock...\n";
        $this->dataService = Mockery::mock(DataService::class);
        
        // Mock common DataService methods
        $this->dataService->shouldReceive('getLastError')
            ->andReturn(false);
            
        $this->dataService->shouldReceive('throwExceptionOnError')
            ->andReturn($this->dataService);
            
        $this->dataService->shouldReceive('getServiceURL')
            ->andReturn('https://sandbox-quickbooks.api.intuit.com');
            
        // Mock OAuth2 methods
        echo "[DEBUG] Setting up OAuth2 mocks...\n";
        $this->oauth2LoginHelper = Mockery::mock(OAuth2LoginHelper::class);
        $this->dataService->shouldReceive('getOAuth2LoginHelper')
            ->andReturn($this->oauth2LoginHelper);
            
        // Create token data with debug info
        $refreshTokenData = [
            'refresh_token' => 'new_refresh_token', 
            'access_token' => 'new_access_token', 
            'expires_in' => 3600
        ];
        
        $authTokenData = [
            'refresh_token' => 'test_refresh_token', 
            'access_token' => 'test_access_token', 
            'expires_in' => 3600
        ];
        
        echo "[DEBUG] Mock refresh token: " . json_encode($refreshTokenData) . "\n";
        echo "[DEBUG] Mock auth token: " . json_encode($authTokenData) . "\n";
            
        $this->oauth2LoginHelper->shouldReceive('refreshToken')
            ->andReturn($refreshTokenData);
            
        $this->oauth2LoginHelper->shouldReceive('exchangeAuthorizationCodeForToken')
            ->andReturn($authTokenData);
        
        // Mock CRUD operations
        echo "[DEBUG] Setting up CRUD operation mocks...\n";
        $mockCustomer = new \QuickBooksOnline\API\Data\IPPCustomer();
        $mockCustomer->Id = '123';
        $mockCustomer->DisplayName = 'Test Customer';
        $mockCustomer->GivenName = 'Test';
        $mockCustomer->FamilyName = 'Customer';
        $mockCustomer->PrimaryEmailAddr = new \QuickBooksOnline\API\Data\IPPEmailAddress();
        $mockCustomer->PrimaryEmailAddr->Address = 'test@example.com';
        
        // Output mock customer details
        echo "[DEBUG] Mock customer: ID={$mockCustomer->Id}, Name={$mockCustomer->DisplayName}, Email={$mockCustomer->PrimaryEmailAddr->Address}\n";
        
        // Mock FindById method
        $this->dataService->shouldReceive('FindById')
            ->with(Mockery::any(), Mockery::any())
            ->andReturnUsing(function($entityName, $id) use ($mockCustomer) {
                echo "[DEBUG] FindById called with: entityName={$entityName}, id={$id}\n";
                return $mockCustomer;
            });
            
        // Mock Add method
        $this->dataService->shouldReceive('Add')
            ->with(Mockery::any())
            ->andReturnUsing(function($entity) use ($mockCustomer) {
                echo "[DEBUG] Add called with entity type: " . get_class($entity) . "\n";
                if (method_exists($entity, 'getDisplayName')) {
                    echo "[DEBUG] Entity DisplayName: " . $entity->getDisplayName() . "\n";
                }
                return $mockCustomer;
            });
            
        // Mock Update method
        $this->dataService->shouldReceive('Update')
            ->with(Mockery::any())
            ->andReturnUsing(function($entity) use ($mockCustomer) {
                echo "[DEBUG] Update called with entity ID: {$entity->Id}\n";
                if (method_exists($entity, 'getDisplayName')) {
                    echo "[DEBUG] Entity DisplayName: " . $entity->getDisplayName() . "\n";
                }
                return $mockCustomer;
            });
            
        // Mock Delete method
        $this->dataService->shouldReceive('Delete')
            ->with(Mockery::any())
            ->andReturnUsing(function($entity) {
                echo "[DEBUG] Delete called with entity ID: {$entity->Id}\n";
                return true;
            });
        
        // Create a QuickBooks instance with our mock DataService
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
    
    /**
     * Mock a service exception for error testing
     *
     * @param string $method The method that should throw an exception
     * @param string $message The exception message
     * @param int $code The exception code
     */
    protected function mockServiceException(string $method, string $message = 'Error', int $code = 400)
    {
        $exception = new ServiceException($message, $code);
        
        $this->dataService->shouldReceive($method)
            ->once()
            ->andThrow($exception);
    }
}
