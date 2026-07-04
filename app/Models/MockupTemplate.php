<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MockupTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'product_type',
        'size',
        'theme',
        'color_scheme',
        'placements',
        'style_notes',
        'image_path',
        'source_image_path',
        'is_ai_generated',
    ];

    protected $casts = [
        'is_ai_generated' => 'boolean',
    ];

    public function mockups()
    {
        return $this->hasMany(Mockup::class, 'template_id');
    }
}
