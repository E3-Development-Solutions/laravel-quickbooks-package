<?php

namespace E3DevelopmentSolutions\QuickBooks\Models;

use Illuminate\Database\Eloquent\Model;

class QuickBooksToken extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'realm_id',
        'expires_at',
        'refresh_token_expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'refresh_token_expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the token.
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
