<?php

namespace E3DevelopmentSolutions\QuickBooks\Services;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Exception\ServiceException;
use E3DevelopmentSolutions\QuickBooks\Exceptions\QuickBooksAuthException;
use E3DevelopmentSolutions\QuickBooks\Models\QuickBooksToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QuickBooksBaseService
{
    /**
     * The QuickBooks DataService instance.
     *
     * @var \QuickBooksOnline\API\DataService\DataService
     */
    protected $dataService;

    /**
     * Create a new QuickBooksBaseService instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->initializeDataService();
    }

    /**
     * Initialize the QuickBooks DataService.
     *
     * @return void
     */
    protected function initializeDataService()
    {
        try {
            $this->dataService = DataService::Configure([
                'auth_mode' => config('quickbooks.auth_mode'),
                'ClientID' => config('quickbooks.client_id'),
                'ClientSecret' => config('quickbooks.client_secret'),
                'RedirectURI' => config('quickbooks.redirect_uri'),
                'scope' => config('quickbooks.scope'),
                'baseUrl' => config('quickbooks.base_url'),
            ]);

            // If user is authenticated, set access token
            if (Auth::check() && Auth::user()->qb_access_token) {
                $this->setTokens();
            }
        } catch (\Exception $e) {
            throw new QuickBooksAuthException('Failed to initialize QuickBooks DataService: ' . $e->getMessage());
        }
    }

    /**
     * Set the access and refresh tokens for the DataService.
     *
     * @return void
     */
    protected function setTokens()
    {
        $user = Auth::user();
        
        if ($user->qb_access_token && $user->qb_realm_id) {
            $this->dataService->updateOAuth2Token($user->qb_access_token);
            $this->dataService->setRealmID($user->qb_realm_id);
            
            // Check if token needs refresh
            $this->refreshIfNeeded();
        }
    }

    /**
     * Refresh the access token if needed.
     *
     * @return void
     */
    protected function refreshIfNeeded()
    {
        $user = Auth::user();
        
        if ($user->qb_token_expires_at && now()->greaterThan($user->qb_token_expires_at)) {
            try {
                $oauth2LoginHelper = new OAuth2LoginHelper(
                    config('quickbooks.client_id'),
                    config('quickbooks.client_secret')
                );
                
                $refreshedAccessTokenObj = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($user->qb_refresh_token);
                
                $this->storeTokens([
                    'access_token' => $refreshedAccessTokenObj->getAccessToken(),
                    'refresh_token' => $refreshedAccessTokenObj->getRefreshToken(),
                    'expires_in' => $refreshedAccessTokenObj->getAccessTokenExpiresIn(),
                ]);
                
                // Update the DataService with the new access token
                $this->dataService->updateOAuth2Token($user->qb_access_token);
            } catch (\Exception $e) {
                throw new QuickBooksAuthException('Failed to refresh QuickBooks token: ' . $e->getMessage());
            }
        }
    }

    /**
     * Store the OAuth tokens in both the user model and tokens table.
     *
     * @param  array  $tokens
     * @return void
     */
    public function storeTokens(array $tokens)
    {
        $user = Auth::user();
        
        // Update user model
        $user->qb_access_token = $tokens['access_token'];
        $user->qb_refresh_token = $tokens['refresh_token'];
        $user->qb_token_expires_at = now()->addSeconds($tokens['expires_in']);
        $user->save();

        // Also store in QuickBooksToken table if realm_id is available
        /*
        if ($user->qb_realm_id) {
            QuickBooksToken::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token'],
                    'realm_id' => $user->qb_realm_id,
                    'expires_at' => now()->addSeconds($tokens['expires_in']),
                    'refresh_token_expires_at' => now()->addMonths(3), // QuickBooks refresh tokens expire after 100 days
                ]
            );
        }
        */
    }

    /**
     * Get the QuickBooks DataService instance.
     *
     * @return \QuickBooksOnline\API\DataService\DataService
     */
    public function getDataService()
    {
        return $this->dataService;
    }

    /**
     * Get the OAuth2 login URL.
     *
     * @param  string|null  $state
     * @return string
     */
    public function getAuthorizationUrl($state = null)
    {
        try {
            $this->initializeDataService();
            $oauth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
            
            // Use configured scope from config
            $scopes = explode(' ', config('quickbooks.scope'));
            
            $authUrl = $oauth2LoginHelper->getAuthorizationCodeURL(
                $scopes,
                $state,
                config('quickbooks.redirect_uri')
            );
            
            return $authUrl;
        } catch (\Exception $e) {
            Log::error('Error generating QuickBooks authorization URL: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw new QuickBooksAuthException('Failed to generate QuickBooks authorization URL: ' . $e->getMessage());
        }
    }

    /**
     * Process the OAuth2 callback and store tokens.
     *
     * @param  string  $code
     * @param  string  $realmId
     * @param  int|null  $userId
     * @return bool
     */
    public function processCallback($code, $realmId, $userId = null)
    {
        try {
            Log::info('Processing QuickBooks callback', [
                'realmId' => $realmId,
                'userId' => $userId,
                'client_id_configured' => !empty(config('quickbooks.client_id')),
                'client_secret_configured' => !empty(config('quickbooks.client_secret')),
                'redirect_uri' => config('quickbooks.redirect_uri')
            ]);

            // Re-initialize DataService to ensure fresh configuration
            $this->initializeDataService();
            
            $oauth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
            
            // Log OAuth configuration
            Log::debug('QuickBooks OAuth configuration', [
                'auth_mode' => config('quickbooks.auth_mode'),
                'redirect_uri' => config('quickbooks.redirect_uri'),
                'scope' => config('quickbooks.scope'),
                'base_url' => config('quickbooks.base_url')
            ]);
            
            $accessTokenObj = $oauth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
            
            // Get the user - first try the provided userId, then try the authenticated user
            $user = null;
            if ($userId) {
                $userModel = config('auth.providers.users.model');
                $user = $userModel::find($userId);
            }
            
            if (!$user) {
                $user = Auth::user();
            }
            
            if (!$user) {
                throw new QuickBooksAuthException('User not found. Please ensure you are logged in.');
            }
            
            Log::info('Found user for QuickBooks callback', [
                'user_id' => $user->id
            ]);
            
            // Store the tokens
            $user->qb_access_token = $accessTokenObj->getAccessToken();
            $user->qb_refresh_token = $accessTokenObj->getRefreshToken();
            $user->qb_token_expires_at = now()->addSeconds($accessTokenObj->getAccessTokenExpiresIn());
            $user->qb_realm_id = $realmId;
            $user->save();

            Log::info('Successfully processed QuickBooks callback', [
                'user_id' => $user->id,
                'realm_id' => $realmId
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('QuickBooks OAuth Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'code' => $code ? '***REDACTED***' : null,
                'realmId' => $realmId,
                'userId' => $userId,
                'request_params' => request()->all()
            ]);
            
            throw new QuickBooksAuthException('Failed to process QuickBooks callback: ' . $e->getMessage());
        }
    }
}
