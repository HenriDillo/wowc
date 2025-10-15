<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class OrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $orders = Order::with('items.item')->where('user_id', $user->id)->latest()->get();
        $grouped = [
            'regular' => [],
            'preorders' => [],
            'backorders' => [],
        ];
        foreach ($orders as $order) {
            $hasPre = $order->items->contains(function ($oi) { return (bool) ($oi->is_preorder ?? false); });
            $hasBack = $order->items->contains(function ($oi) { return (bool) ($oi->is_backorder ?? false); });
            if ($hasPre) {
                $grouped['preorders'][] = $order;
            } elseif ($hasBack) {
                $grouped['backorders'][] = $order;
            } else {
                $grouped['regular'][] = $order;
            }
        }
        return response()->json($grouped);
    }

    public function show($id)
    {
        $user = Auth::user();
        $order = Order::with('items.item')->where('user_id', $user->id)->findOrFail($id);
        return response()->json($order);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'order_type' => 'required|in:standard,backorder,preorder',
            'payment_method' => 'nullable|in:COD,GCash,Card',
            'address' => 'nullable|array',
            'address.address_line' => 'nullable|string|max:255',
            'address.city' => 'nullable|string|max:120',
            'address.province' => 'nullable|string|max:120',
            'address.postal_code' => 'nullable|string|max:20',
            'address.phone_number' => 'nullable|string|max:30',
        ]);

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        $orderType = $data['order_type'];

        $subtotal = 0.0;
        foreach ($cart as $entry) {
            $subtotal += $entry['price'] * $entry['quantity'];
        }
        $tax = 0.0; // Extend if needed
        $total = $subtotal + $tax;

        $createdOrder = DB::transaction(function () use ($user, $orderType, $total, $cart) {
            $order = new Order([
                'user_id' => $user->id,
                'order_type' => $orderType,
                'status' => 'pending',
                'total_amount' => $total,
                'payment_method' => request('payment_method'),
                'payment_status' => 'unpaid',
            ]);
            $order->save();

            foreach ($cart as $entry) {
                $item = Item::lockForUpdate()->findOrFail($entry['item_id']);
                $quantity = (int) $entry['quantity'];
                $price = (float) $entry['price'];

                if ($orderType === 'standard') {
                    if ($item->status === 'pre_order' || $item->status === 'back_order') {
                        // Pre/back order: do not reduce stock
                    } else if ($item->stock >= $quantity) {
                        $item->stock -= $quantity;
                        $item->save();
                    } else {
                        abort(response()->json(['error' => 'Not enough stock for item '.$item->name], 400));
                    }
                } elseif ($orderType === 'backorder') {
                    $order->status = 'backorder';
                } elseif ($orderType === 'preorder') {
                    if ($item->release_date && $item->release_date->isFuture()) {
                        $order->status = 'preorder';
                    } else {
                        abort(response()->json(['error' => 'This item is not available for preorder: '.$item->name], 400));
                    }
                }

                $subtotalItem = $price * $quantity;
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotalItem,
                    'is_preorder' => $item->status === 'pre_order',
                    'is_backorder' => $item->status === 'back_order',
                ]);
            }

            // Persist any status change performed inside items loop
            $order->save();

            return $order->fresh('items.item');
        });

        // Clear cart after successful order
        Session::forget('cart');

        return response()->json($createdOrder, 201);
    }
}


