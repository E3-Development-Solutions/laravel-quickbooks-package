<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests\Unit;

use E3DevelopmentSolutions\QuickBooks\Facades\QuickBooks as QuickBooksFacade;
use E3DevelopmentSolutions\QuickBooks\QuickBooks;
use E3DevelopmentSolutions\QuickBooks\QuickBooksServiceProvider;
use E3DevelopmentSolutions\QuickBooks\Tests\TestCase;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;

class QuickBooksServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_the_quickbooks_service()
    {
        // Act
        $service = $this->app->make('quickbooks');
        
        // Assert
        $this->assertInstanceOf(QuickBooks::class, $service);
    }
    
    /** @test */
    public function it_provides_the_quickbooks_facade()
    {
        // Act
        $facade = QuickBooksFacade::getFacadeRoot();
        
        // Assert
        $this->assertInstanceOf(QuickBooks::class, $facade);
    }
    
    /** @test */
    public function it_publishes_configuration()
    {
        // Arrange
        $provider = $this->app->getProvider(QuickBooksServiceProvider::class);
        
        // Act
        $publishes = $provider->publishes();
        
        // Assert
        $this->assertArrayHasKey(QuickBooksServiceProvider::class, $publishes);
        $this->assertArrayHasKey(QuickBooksServiceProvider::class.'-config', $publishes);
    }
}
