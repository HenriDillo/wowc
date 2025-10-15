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
        $orders = Order::with('items.item')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('customer-orders', [
            'orders' => $orders,
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();
        $order = Order::with(['user.address', 'items.item.photos'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $hasPreorder = $order->items->contains(fn($oi) => (bool) ($oi->is_preorder ?? false));
        $hasBackorder = $order->items->contains(fn($oi) => (bool) ($oi->is_backorder ?? false));

        return view('order-confirmation', compact('order', 'hasPreorder', 'hasBackorder'));
    }
}


