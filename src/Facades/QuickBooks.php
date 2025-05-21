<?php

namespace E3DevelopmentSolutions\QuickBooks\Facades;

use Illuminate\Support\Facades\Facade;

class QuickBooks extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'quickbooks';
    }
}
