<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MockupBackground extends Model
{
    protected $fillable = [
        'name',
        'image_path',
        'theme',
        'lighting',
        'color_scheme',
        'size',
        'created_by',
    ];

    public function templates()
    {
        return $this->hasMany(MockupTemplate::class, 'background_id');
    }
}
