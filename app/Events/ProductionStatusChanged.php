<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * PHASE-1.5: Fired by OrderService::transitionProductionStatus after every
 * successful custom-print production step. Phase 4 automations subscribe.
 */
class ProductionStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public ?string $from,
        public string $to,
    ) {
    }
}
