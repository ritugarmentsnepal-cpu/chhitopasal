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
        'image_path',
    ];
}
