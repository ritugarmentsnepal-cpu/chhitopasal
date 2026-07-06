<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiGeneration extends Model
{
    protected $fillable = [
        'type',
        'model',
        'image_path',
        'template_id',
        'mockup_id',
        'user_id',
        'cost_estimate',
    ];

    protected $casts = [
        'cost_estimate' => 'decimal:4',
    ];

    /**
     * An attempt is "confirmed" once it became a saved template or mockup.
     */
    public function scopeUnconfirmed($query)
    {
        return $query->whereNull('template_id')->whereNull('mockup_id');
    }
}
