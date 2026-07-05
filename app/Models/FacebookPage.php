<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookPage extends Model
{
    protected $fillable = [
        'user_id',
        'page_id',
        'page_name',
        'access_token',
        'picture_url',
    ];

    /**
     * SEC: page access tokens are encrypted at rest.
     * Existing plaintext rows are converted by the 2026_07_05_120000 migration.
     */
    protected $casts = [
        'access_token' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
