<?php

namespace E3DevelopmentSolutions\QuickBooks;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Data\IPPCustomer;
use QuickBooksOnline\API\Exception\ServiceException;

class QuickBooks
{
    /**
     * The DataService instance.
     *
     * @var DataService
     */
    protected $dataService;

    /**
     * Create a new QuickBooks instance.
     *
     * @param  DataService  $dataService
     * @return void
     */
    public function __construct(DataService $dataService)
    {
        $this->dataService = $dataService;
    }

    /**
     * Get the DataService instance.
     *
     * @return DataService
     */
    public function getDataService(): DataService
    {
        return $this->dataService;
    }

    /**
     * Create a new customer in QuickBooks.
     *
     * @param  array  $data
     * @return \QuickBooksOnline\API\Data\IPPCustomer
     * @throws \Exception
     */
    public function createCustomer(array $data)
    {
        $customer = new IPPCustomer();
        
        // Map the data to the customer object
        foreach ($data as $key => $value) {
            $customer->$key = $value;
        }
        
        $result = $this->dataService->Add($customer);
        
        if ($error = $this->dataService->getLastError()) {
            throw new \Exception($error->getResponseBody());
        }
        
        return $result;
    }

    /**
     * Get a customer by ID.
     *
     * @param  string  $id
     * @return \QuickBooksOnline\API\Data\IPPCustomer|null
     */
    public function getCustomer($id)
    {
        return $this->dataService->FindById('customer', $id);
    }

    /**
     * Update a customer in QuickBooks.
     *
     * @param  string  $id
     * @param  array  $data
     * @return \QuickBooksOnline\API\Data\IPPCustomer
     * @throws \Exception
     */
    public function updateCustomer($id, array $data)
    {
        // First, get the existing customer
        $customer = $this->getCustomer($id);
        
        if (!$customer) {
            throw new \Exception("Customer not found");
        }
        
        // Update the customer with new data
        foreach ($data as $key => $value) {
            $customer->$key = $value;
        }
        
        $result = $this->dataService->Update($customer);
        
        if ($error = $this->dataService->getLastError()) {
            throw new \Exception($error->getResponseBody());
        }
        
        return $result;
    }

    /**
     * Delete a customer from QuickBooks.
     *
     * @param  string  $id
     * @return bool
     */
    public function deleteCustomer($id)
    {
        $customer = $this->getCustomer($id);
        
        if (!$customer) {
            return false;
        }
        
        $result = $this->dataService->Delete($customer);
        
        if ($error = $this->dataService->getLastError()) {
            throw new \Exception($error->getResponseBody());
        }
        
        return $result;
    }

    /**
     * Get the base URL of the QuickBooks DataService.
     *
     * @return string
     */
    public function getDataServiceBaseUrl(): string
    {
        return $this->dataService->getServiceURL();
    }

    /**
     * Dynamically pass method calls to the DataService instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->dataService, $method)) {
            try {
                return $this->dataService->$method(...$parameters);
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf(
                    'Error calling method %s::%s: %s', get_class($this->dataService), $method, $e->getMessage()
                ), 0, $e);
            }
        }

        throw new \BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', get_class($this->dataService), $method
        ));
    }
}
