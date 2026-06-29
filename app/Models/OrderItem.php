<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderItemFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price_at_purchase',
        'cost_at_purchase',
        'color',
        'size',
        'size_breakdown',
        'custom_notes',
        'returned_good_qty',
        'returned_damaged_qty',
    ];

    protected $casts = [
        'size_breakdown' => 'array',
    ];

    /**
     * Calculate total quantity from size breakdown JSON.
     * Falls back to the quantity column if no breakdown exists.
     */
    public function getTotalQuantityFromBreakdown(): int
    {
        if (!empty($this->size_breakdown) && is_array($this->size_breakdown)) {
            return array_sum($this->size_breakdown);
        }

        return $this->quantity;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
