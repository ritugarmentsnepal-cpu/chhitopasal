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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
