<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use \App\Traits\Loggable;

    protected $fillable = [
        'supplier_name',
        'reference_no',
        'total_amount',
        'date',
        'status',
        'notes',
        'party_id',
        'payment_status',
        'paid_amount',
        'attachment_path'
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }
}
