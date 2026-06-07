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
        'bundle_only',
        'color_options',
        'size_options',
        'is_flash_sale',
        'flash_sale_price',
    ];

    protected $casts = [
        'additional_images' => 'array',
        'bundles' => 'array',
        'bundle_only' => 'boolean',
        'color_options' => 'array',
        'size_options' => 'array',
        'is_flash_sale' => 'boolean',
    ];

    /**
     * FRONT-01: Expose a boolean instead of exact stock count for public pages.
     */
    protected $appends = ['in_stock', 'original_price'];

    public function getInStockAttribute(): bool
    {
        return ($this->stock ?? 0) > 0;
    }

    public function getOriginalPriceAttribute()
    {
        return $this->attributes['price'] ?? 0;
    }

    public function getPriceAttribute($value)
    {
        if ($this->is_flash_sale && !is_null($this->flash_sale_price)) {
            return $this->flash_sale_price;
        }
        return $value;
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
