<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;

/**
 * PHASE-3: global search — jump to any order, customer, or product from
 * the topbar (Ctrl+K / ⌘K). Results respect the user's permissions.
 */
class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        // Min 2 chars, except pure numbers (short order IDs like "7" are valid)
        if (mb_strlen($q) < 2 && !ctype_digit($q)) {
            return response()->json(['groups' => []]);
        }

        $like = '%' . OrderService::escapeLike($q) . '%';
        $user = auth()->user();
        $groups = [];

        if ($user->hasPermission('orders')) {
            $orders = Order::query()
                ->where(function ($w) use ($q, $like) {
                    if (ctype_digit($q)) {
                        $w->where('id', $q);
                    }
                    $w->orWhere('customer_name', 'like', $like)
                      ->orWhere('customer_phone', 'like', $like)
                      ->orWhere('pathao_consignment_id', 'like', $like);
                })
                ->orderByDesc('id')
                ->limit(5)
                ->get(['id', 'customer_name', 'customer_phone', 'status', 'total_amount', 'order_type']);

            if ($orders->isNotEmpty()) {
                $groups[] = [
                    'label' => 'Orders',
                    'items' => $orders->map(fn ($o) => [
                        'title' => "#{$o->id} · {$o->customer_name}",
                        'sub' => ucwords(str_replace('_', ' ', $o->status)) . ' · Rs. ' . number_format((float) $o->total_amount)
                            . ($o->order_type === 'custom_print' ? ' · Custom Print' : ''),
                        'url' => route('orders.show', $o->id),
                    ])->values(),
                ];
            }
        }

        if ($user->hasPermission('customers')) {
            $customers = Order::query()
                ->where(function ($w) use ($like) {
                    $w->where('customer_name', 'like', $like)
                      ->orWhere('customer_phone', 'like', $like);
                })
                ->selectRaw('customer_phone, MAX(customer_name) as customer_name, COUNT(*) as orders_count')
                ->groupBy('customer_phone')
                ->orderByDesc('orders_count')
                ->limit(4)
                ->get();

            if ($customers->isNotEmpty()) {
                $groups[] = [
                    'label' => 'Customers',
                    'items' => $customers->map(fn ($c) => [
                        'title' => $c->customer_name,
                        'sub' => $c->customer_phone . ' · ' . $c->orders_count . ' order' . ($c->orders_count > 1 ? 's' : ''),
                        'url' => route('customers.show', $c->customer_phone),
                    ])->values(),
                ];
            }
        }

        if ($user->hasPermission('products')) {
            $products = Product::where('name', 'like', $like)
                ->limit(4)
                ->get(['id', 'name', 'price', 'stock']);

            if ($products->isNotEmpty()) {
                $groups[] = [
                    'label' => 'Products',
                    'items' => $products->map(fn ($p) => [
                        'title' => $p->name,
                        'sub' => 'Rs. ' . number_format((float) $p->price) . ' · ' . $p->stock . ' in stock',
                        'url' => route('products.index'),
                    ])->values(),
                ];
            }
        }

        return response()->json(['groups' => $groups]);
    }
}
