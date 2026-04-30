<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\Loggable;


    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'cost_price',
        'weight_grams',
        'stock',
        'category_id',
        'image_path',
        'additional_images',
        'video_path',
        'bundles',
    ];

    protected $casts = [
        'additional_images' => 'array',
        'bundles' => 'array',
    ];

    /**
     * FRONT-01: Expose a boolean instead of exact stock count for public pages.
     */
    protected $appends = ['in_stock'];

    public function getInStockAttribute(): bool
    {
        return ($this->stock ?? 0) > 0;
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
