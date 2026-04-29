<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'has_color_variants', 'has_size_variants', 'color_options', 'size_options'];

    protected $casts = [
        'has_color_variants' => 'boolean',
        'has_size_variants' => 'boolean',
        'color_options' => 'array',
        'size_options' => 'array',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
