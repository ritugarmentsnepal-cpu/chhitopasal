<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'page_id',
        'thread_id',
        'customer_name',
        'customer_facebook_id',
        'category',
        'description',
        'status',
        'priority',
        'assigned_to',
        'resolved_at',
        'notes',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    const CATEGORIES = [
        'late_delivery' => 'Late Delivery',
        'wrong_product' => 'Wrong Product',
        'damaged_product' => 'Damaged Product',
        'refund' => 'Refund Request',
        'payment_issue' => 'Payment Issue',
        'general_inquiry' => 'General Inquiry',
        'other' => 'Other',
    ];

    const STATUSES = [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];

    const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function threadState()
    {
        return $this->hasOne(AiThreadState::class, 'ticket_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}
