<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests\Integration;

use E3DevelopmentSolutions\QuickBooks\Models\QuickBooksToken;
use E3DevelopmentSolutions\QuickBooks\Tests\TestCase;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Data\IPPCustomer;

/**
 * Integration tests for QuickBooks API
 * 
 * These tests connect to the actual QuickBooks API and perform real operations.
 * They are skipped by default and should only be run manually for troubleshooting.
 */
class QuickBooksIntegrationTest extends TestCase
{
    /**
     * @var DataService
     */
    protected $dataService;
    
    /**
     * @var string
     */
    protected $realmId;
    
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Skip these tests by default
        if (!env('RUN_INTEGRATION_TESTS', false)) {
            $this->markTestSkipped('Integration tests are disabled. Set RUN_INTEGRATION_TESTS=true in .env to run them.');
        }
        
        // Check if we have the required environment variables
        if (!env('QUICKBOOKS_CLIENT_ID') || !env('QUICKBOOKS_CLIENT_SECRET') || !env('QUICKBOOKS_REALM_ID')) {
            $this->markTestSkipped('QuickBooks credentials not found in .env file.');
        }
        
        $this->realmId = env('QUICKBOOKS_REALM_ID');
        
        // Set up a real DataService instance
        $this->setupRealDataService();
    }
    
    /**
     * Set up a real DataService instance for integration testing
     */
    protected function setupRealDataService()
    {
        echo "\n[INTEGRATION] Setting up real QuickBooks DataService...\n";
        
        // Check if we have a token in the database
        $token = QuickBooksToken::where('realm_id', $this->realmId)->first();
        
        if (!$token) {
            $this->markTestSkipped('No QuickBooks token found for realm ID: ' . $this->realmId . '. Please authenticate first.');
        }
        
        echo "[INTEGRATION] Using token with ID: {$token->id}, expires at: {$token->expires_at}\n";
        
        // Check if token is expired
        if ($token->expires_at < now()) {
            echo "[INTEGRATION] Token is expired, attempting to refresh...\n";
            $this->refreshToken($token);
        }
        
        // Create DataService with the token
        $this->dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => env('QUICKBOOKS_CLIENT_ID'),
            'ClientSecret' => env('QUICKBOOKS_CLIENT_SECRET'),
            'accessTokenKey' => $token->access_token,
            'refreshTokenKey' => $token->refresh_token,
            'QBORealmID' => $token->realm_id,
            'baseUrl' => env('QUICKBOOKS_BASE_URL', 'development')
        ]);
        
        $this->dataService->throwExceptionOnError(true);
        
        echo "[INTEGRATION] DataService configured successfully\n";
    }
    
    /**
     * Refresh an expired token
     */
    protected function refreshToken(QuickBooksToken $token)
    {
        echo "[INTEGRATION] Refreshing token...\n";
        
        $oauth2LoginHelper = new OAuth2LoginHelper(
            env('QUICKBOOKS_CLIENT_ID'),
            env('QUICKBOOKS_CLIENT_SECRET'),
            env('QUICKBOOKS_REDIRECT_URI'),
            env('QUICKBOOKS_SCOPE', 'com.intuit.quickbooks.accounting')
        );
        
        try {
            $refreshedTokenArray = $oauth2LoginHelper->refreshToken($token->refresh_token);
            
            echo "[INTEGRATION] Token refreshed successfully\n";
            
            // Update token in database
            $token->update([
                'access_token' => $refreshedTokenArray['access_token'],
                'refresh_token' => $refreshedTokenArray['refresh_token'],
                'expires_at' => now()->addSeconds($refreshedTokenArray['expires_in']),
                'refresh_token_expires_at' => now()->addMonths(3), // QuickBooks refresh tokens expire after 100 days
            ]);
            
            echo "[INTEGRATION] Token updated in database\n";
        } catch (\Exception $e) {
            $this->markTestSkipped('Failed to refresh token: ' . $e->getMessage());
        }
    }
    
    /** @test */
    public function it_can_connect_to_quickbooks_api()
    {
        echo "\n[INTEGRATION] Testing connection to QuickBooks API...\n";
        
        // Get the QuickBooks company info as a simple test
        $companyInfo = $this->dataService->getCompanyInfo();
        
        // Output company info details
        echo "[INTEGRATION] Connected to company: {$companyInfo->CompanyName}\n";
        echo "[INTEGRATION] Company address: {$companyInfo->CompanyAddr->Line1}, {$companyInfo->CompanyAddr->City}, {$companyInfo->CompanyAddr->CountrySubDivisionCode}\n";
        
        // Assert that we got a valid response
        $this->assertNotNull($companyInfo);
        $this->assertNotEmpty($companyInfo->CompanyName);
    }
    
    /** @test */
    public function it_can_retrieve_customers_from_api()
    {
        echo "\n[INTEGRATION] Retrieving customers from QuickBooks API...\n";
        
        // Query for customers
        $customers = $this->dataService->Query("SELECT * FROM Customer MAXRESULTS 5");
        
        // Check if we got any customers
        if (empty($customers) || count($customers) == 0) {
            echo "[INTEGRATION] No customers found in QuickBooks\n";
            $this->markTestSkipped('No customers found in QuickBooks');
        }
        
        // Output customer details
        echo "[INTEGRATION] Found " . count($customers) . " customers:\n";
        foreach ($customers as $customer) {
            echo "[INTEGRATION] Customer ID: {$customer->Id}, Name: {$customer->DisplayName}\n";
        }
        
        // Assert that we got customers
        $this->assertNotEmpty($customers);
        $this->assertInstanceOf(IPPCustomer::class, $customers[0]);
    }
}
