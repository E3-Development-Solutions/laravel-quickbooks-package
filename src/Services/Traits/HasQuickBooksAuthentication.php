<?php

namespace E3DevelopmentSolutions\QuickBooks\Services\Traits;

use Illuminate\Support\Facades\Redirect;

trait HasQuickBooksAuthentication
{
    /**
     * Get the QuickBooks access token.
     *
     * @return string|null
     */
    public function getQuickBooksAccessToken()
    {
        return $this->qb_access_token;
    }

    /**
     * Get the QuickBooks refresh token.
     *
     * @return string|null
     */
    public function getQuickBooksRefreshToken()
    {
        return $this->qb_refresh_token;
    }

    /**
     * Get the QuickBooks token expiration timestamp.
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getQuickBooksTokenExpiresAt()
    {
        return $this->qb_token_expires_at;
    }

    /**
     * Get the QuickBooks realm ID.
     *
     * @return string|null
     */
    public function getQuickBooksRealmId()
    {
        return $this->qb_realm_id;
    }

    /**
     * Check if the user has a valid QuickBooks connection.
     *
     * @return bool
     */
    public function hasQuickBooksConnection()
    {
        return !empty($this->qb_access_token) && !empty($this->qb_refresh_token) && $this->qb_token_expires_at > now();
    }

    /**
     * Disconnect the user from QuickBooks.
     *
     * @return bool
     */
    public function disconnectFromQuickBooks()
    {
        $this->qb_access_token = null;
        $this->qb_refresh_token = null;
        $this->qb_token_expires_at = null;
        $this->qb_realm_id = null;
        
        return $this->save();
    }

    public function connectToQuickBooks()
    {
        // Assumes you have a named route for QuickBooks connect, e.g., 'quickbooks.connect'
        return redirect()->route('quickbooks.connect');
    }
}
