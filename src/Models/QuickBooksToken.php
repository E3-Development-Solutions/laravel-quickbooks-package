<?php

namespace E3DevelopmentSolutions\QuickBooks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class QuickBooksToken extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quickbooks_tokens';
    
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
     * The attributes that should be encrypted.
     *
     * @var array<int, string>
     */
    protected $encrypted = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get the user that owns the token.
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
    
    /**
     * Find a token by realm ID.
     *
     * @param string $realmId
     * @return \E3DevelopmentSolutions\QuickBooks\Models\QuickBooksToken|null
     */
    public static function findByRealmId($realmId)
    {
        return static::where('realm_id', $realmId)->first();
    }
    
    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encrypted) && !is_null($value)) {
            $value = Crypt::encrypt($value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get an attribute from the model.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encrypted) && !is_null($value)) {
            return Crypt::decrypt($value);
        }

        return $value;
    }
}
