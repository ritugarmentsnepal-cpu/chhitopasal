<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorEvent extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // Events usually only need created_at

    protected $fillable = [
        'session_id',
        'event_type',
        'product_id',
        'category_id',
        'url'
    ];

    public function session()
    {
        return $this->belongsTo(VisitorSession::class, 'session_id', 'session_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
