<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();
		$orders = Order::with('items.item.photos')
			->select('orders.*') // ensure unique orders even if future joins/filters are added
			->distinct()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // Also fetch active backorders for the customer
        $backOrders = \App\Models\OrderItem::with(['order', 'item'])
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('is_backorder', true)
            ->whereIn('backorder_status', [\App\Models\OrderItem::BO_PENDING, \App\Models\OrderItem::BO_IN_PROGRESS])
            ->orderByDesc('created_at')
            ->get();
            // Fetch custom orders for the customer
            $customOrders = \App\Models\CustomOrder::with(['order'])
                ->whereHas('order', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->whereIn('status', ['pending_review', 'approved', 'in_production'])
                ->latest()
                ->get();

            return view('customer-orders', [
                'orders' => $orders,
                'backOrders' => $backOrders,
                'customOrders' => $customOrders,
            ]);
    }

    public function show($id)
    {
        $user = Auth::user();
		$order = Order::with(['user.address', 'items.item.photos', 'payments'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

    $hasBackorder = $order->items->contains(fn($oi) => (bool) ($oi->is_backorder ?? false));

    return view('order-confirmation', compact('order', 'hasBackorder'));
    }
}


