<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mockup extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'template_id',
        'image_path',
        'logo_path',
        'order_id',
        'created_by',
        'tags',
        'share_token',
        'approval_status',
        'approval_feedback',
        'approval_responded_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'approval_responded_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────

    public function template()
    {
        return $this->belongsTo(MockupTemplate::class, 'template_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
