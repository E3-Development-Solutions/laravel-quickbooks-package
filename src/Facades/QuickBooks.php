<?php

namespace E3DevelopmentSolutions\QuickBooks\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \QuickBooksOnline\API\DataService\DataService getDataService()
 * @method static string getDataServiceBaseUrl()
 * @method static mixed __call(string $method, array $parameters)
 *
 * @see \E3DevelopmentSolutions\QuickBooks\QuickBooks
 */
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
