<?php

namespace E3DevelopmentSolutions\QuickBooks\Services\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;

trait HasQuickBooksAuthentication
{
    /**
     * Get the QuickBooks access token.
     *
     * @return string|null
     */
    public function getQuickBooksAccessToken(): ?string
    {
        return $this->qb_access_token;
    }

    /**
     * Get the QuickBooks refresh token.
     *
     * @return string|null
     */
    public function getQuickBooksRefreshToken(): ?string
    {
        return $this->qb_refresh_token;
    }

    /**
     * Get the QuickBooks token expiration timestamp.
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getQuickBooksTokenExpiresAt(): ?Carbon
    {
        return $this->qb_token_expires_at;
    }

    /**
     * Get the QuickBooks realm ID.
     *
     * @return string|null
     */
    public function getQuickBooksRealmId(): ?string
    {
        return $this->qb_realm_id;
    }

    /**
     * Check if the user has a valid QuickBooks connection.
     *
     * @return bool
     */
    public function hasQuickBooksConnection(): bool
    {
        return !empty($this->qb_access_token) 
            && !empty($this->qb_refresh_token) 
            && $this->qb_token_expires_at 
            && $this->qb_token_expires_at > now();
    }
    
    /**
     * Alias for hasQuickBooksConnection for backward compatibility.
     *
     * @return bool
     */
    public function isConnectedToQuickBooks(): bool
    {
        return $this->hasQuickBooksConnection();
    }

    /**
     * Disconnect the user from QuickBooks.
     *
     * @return bool
     */
    public function disconnectFromQuickBooks(): bool
    {
        $this->qb_access_token = null;
        $this->qb_refresh_token = null;
        $this->qb_token_expires_at = null;
        $this->qb_realm_id = null;
        
        return $this->save();
    }

    /**
     * Connect to QuickBooks.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function connectToQuickBooks()
    {
        return redirect()->route('quickbooks.connect');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function getCasts(): array
    {
        return array_merge(parent::getCasts(), [
            'qb_token_expires_at' => 'datetime',
        ]);
    }
}
