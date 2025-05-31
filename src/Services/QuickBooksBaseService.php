<?php

namespace E3DevelopmentSolutions\QuickBooks\Services;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Exception\ServiceException;
use E3DevelopmentSolutions\QuickBooks\Exceptions\QuickBooksAuthException;
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
        
        if ($user->qb_token_expires && now()->greaterThan($user->qb_token_expires)) {
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
     * Store the OAuth tokens in the user model.
     *
     * @param  array  $tokens
     * @return void
     */
    public function storeTokens(array $tokens)
    {
        $user = Auth::user();
        $user->qb_access_token = $tokens['access_token'];
        $user->qb_refresh_token = $tokens['refresh_token'];
        $user->qb_token_expires = now()->addSeconds($tokens['expires_in']);
        $user->save();
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
            
            $scopes = [
                \QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper::SCOPE_OPENID,
                \QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper::SCOPE_EMAIL,
                \QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper::SCOPE_PROFILE,
                \QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper::SCOPE_PHONE,
                \QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper::SCOPE_ADDRESS,
                \QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper::SCOPE_ACCOUNTING,
            ];
            
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
            $oauth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
            $accessTokenObj = $oauth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
            
            // Get the user model instance
            $userModel = config('auth.providers.users.model');
            $user = $userId ? $userModel::find($userId) : Auth::user();
            
            if (!$user) {
                throw new QuickBooksAuthException('User not found.');
            }
            
            // Store the tokens in the user model
            $user->qb_access_token = $accessTokenObj->getAccessToken();
            $user->qb_refresh_token = $accessTokenObj->getRefreshToken();
            $user->qb_token_expires_at = now()->addSeconds($accessTokenObj->getAccessTokenExpiresIn());
            $user->qb_realm_id = $realmId;
            $user->save();
            
            return true;
        } catch (\Exception $e) {
            Log::error('QuickBooks OAuth Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'code' => $code ? '***REDACTED***' : null,
                'realmId' => $realmId,
                'userId' => $userId,
            ]);
            
            throw new QuickBooksAuthException('Failed to process QuickBooks callback: ' . $e->getMessage());
        }
    }
}
