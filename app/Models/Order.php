<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\Loggable;

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
        'order_type',
        'design_file',
        'design_files',
        'design_notes',
        'print_method',
        'print_positions',
        'production_status',
        'production_notes',
        'estimated_delivery_date',
        'advance_amount',
        'bulk_batch_id',
        'bulk_ship_batch_id',
        'pathao_consignment_id',
        'pathao_status',
        'pathao_status_updated_at',
        'payment_status',
        'paid_amount',
        'return_verified_at',
        'return_notes',
        'remarks',
        'session_id',
        'shipped_at',
        'mockup_files',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'estimated_delivery_date' => 'date',
        'print_positions' => 'array',
        'design_files' => 'array',
        'mockup_files' => 'array',
        'advance_amount' => 'decimal:2',
    ];

    // ── Helpers ──────────────────────────────────────────

    public function isCustomPrint(): bool
    {
        return $this->order_type === 'custom_print';
    }

    public function isStandard(): bool
    {
        return $this->order_type === 'standard';
    }

    /**
     * Human-readable production status label.
     */
    public function getProductionStatusLabelAttribute(): string
    {
        return match ($this->production_status) {
            'design_received' => 'Design Received',
            'design_approved' => 'Design Approved',
            'in_production' => 'In Production',
            'quality_check' => 'Quality Check',
            'ready_to_ship' => 'Ready to Ship',
            default => 'N/A',
        };
    }

    /**
     * All valid production statuses in order.
     */
    public static function productionStatuses(): array
    {
        return [
            'design_received',
            'design_approved',
            'in_production',
            'quality_check',
            'ready_to_ship',
        ];
    }

    // ── Scopes ───────────────────────────────────────────

    public function scopeCustomPrint($query)
    {
        return $query->where('order_type', 'custom_print');
    }

    public function scopeStandard($query)
    {
        return $query->where('order_type', 'standard');
    }

    // ── Relationships ────────────────────────────────────

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

    public function libraryMockups()
    {
        return $this->hasMany(Mockup::class);
    }
}
