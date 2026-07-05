<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * PHASE-1.5: Fired by OrderService::transitionStatus after every
 * successful status change. Phase 4 automations subscribe to this.
 */
class OrderStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $from,
        public string $to,
    ) {
    }
}
