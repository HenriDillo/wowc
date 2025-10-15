<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    public function index(User $user)
    {
        $orders = $user->orders()->with('items')->latest()->get();
        return response()->json($orders);
    }

    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'order_type' => 'required|in:standard,backorder,preorder,custom',
            'status' => 'nullable|in:pending,processing,completed,cancelled',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|in:COD,GCash,Card',
            'payment_status' => 'nullable|in:unpaid,paid,refunded',
        ]);

        $order = new Order($validated);
        $order->user()->associate($user);
        $order->save();

        return response()->json($order->fresh(), 201);
    }
}


