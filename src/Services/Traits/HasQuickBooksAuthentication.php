<?php

namespace E3DevelopmentSolutions\QuickBooks\Services\Traits;

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
    public function getQuickBooksTokenExpires()
    {
        return $this->qb_token_expires;
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
     * Determine if the user is connected to QuickBooks.
     *
     * @return bool
     */
    public function isConnectedToQuickBooks()
    {
        return ! is_null($this->qb_access_token) && ! is_null($this->qb_realm_id);
    }

    /**
     * Determine if the QuickBooks token needs to be refreshed.
     *
     * @return bool
     */
    public function needsQuickBooksTokenRefresh()
    {
        if (! $this->isConnectedToQuickBooks()) {
            return false;
        }

        return now()->greaterThan($this->qb_token_expires);
    }

    /**
     * Disconnect from QuickBooks.
     *
     * @return void
     */
    public function disconnectFromQuickBooks()
    {
        $this->qb_access_token = null;
        $this->qb_refresh_token = null;
        $this->qb_token_expires = null;
        $this->qb_realm_id = null;
        $this->save();
    }
}
