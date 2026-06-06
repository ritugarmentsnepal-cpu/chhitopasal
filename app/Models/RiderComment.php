<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'pathao_issue_id',
        'rider_comment',
        'admin_reply',
        'status',
        'assigned_user_id',
        'tag',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
