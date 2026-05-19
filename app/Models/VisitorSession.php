<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'ip_address',
        'user_agent',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'fbclid',
        'landing_page_url'
    ];

    public function events()
    {
        return $this->hasMany(VisitorEvent::class, 'session_id', 'session_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'session_id', 'session_id');
    }
}
