<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, \App\Traits\Loggable;

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'address',
        'city',
        'pathao_city_id',
        'pathao_zone_id',
        'pathao_area_id',
        'total_amount',
        'delivery_charge',
        'status',
        'source',
        'pathao_consignment_id',
        'pathao_status',
        'pathao_status_updated_at',
        'payment_status',
        'paid_amount',
        'return_verified_at',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'reference_id')
            ->whereIn('reference_type', [
                \App\SystemAccounts::REF_ORDER,
                \App\SystemAccounts::REF_ORDER_DELIVERED,
                \App\SystemAccounts::REF_SALE_RETURN,
            ]);
    }
}
