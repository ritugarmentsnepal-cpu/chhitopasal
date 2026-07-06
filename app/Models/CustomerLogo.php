<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerLogo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'customer_name',
        'customer_phone',
        'file_path',
        'created_by',
    ];

    public function mockups()
    {
        return $this->hasMany(Mockup::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
