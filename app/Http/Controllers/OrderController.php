<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemStockTransaction;
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
            'backorders' => [],
        ];
        foreach ($orders as $order) {
            $hasBack = $order->items->contains(function ($oi) { return (bool) ($oi->is_backorder ?? false); });
            if ($hasBack) {
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
            'order_type' => 'required|in:standard,backorder',
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

                // Determine whether this cart line should be treated as backorder
                $entryBack = isset($entry['is_backorder']) ? (bool) $entry['is_backorder'] : ($item->status === 'back_order');

                if ($orderType === 'backorder') {
                    // Entire order treated as backorder: do not reduce stock for any line
                    // Automatically set status to "processing" (Awaiting Stock) for back orders
                    $order->status = 'processing';
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $item->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $price * $quantity,
                        'is_backorder' => true,
                        'backorder_status' => \App\Models\OrderItem::BO_PENDING,
                    ]);
                    continue;
                }

                // For standard orders, respect cart-line backorder flag first
                if ($entryBack) {
                    // Do not attempt to fulfill stock; create backorder order item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $item->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $price * $quantity,
                        'is_backorder' => true,
                        'backorder_status' => \App\Models\OrderItem::BO_PENDING,
                    ]);
                    continue;
                }

                // If item is flagged as backorder-only at the product level, treat whole qty as backorder
                if ($item->status === 'back_order') {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $item->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $price * $quantity,
                        'is_backorder' => true,
                        'backorder_status' => \App\Models\OrderItem::BO_PENDING,
                    ]);
                    continue;
                }

                // Otherwise try to fulfill up to available stock and create a backorder for any remainder
                $available = (int) max(0, $item->stock ?? 0);
                if ($available >= $quantity) {
                    $item->stock = $available - $quantity;
                    $item->save();

                    // Check for duplicate transaction to prevent duplicates
                    // Look for existing transaction with same order, item, quantity within last minute
                    $existingTransaction = ItemStockTransaction::where('item_id', $item->id)
                        ->where('type', 'out')
                        ->where('quantity', $quantity)
                        ->where('remarks', "Order #{$order->id} - Customer order fulfillment")
                        ->where('created_at', '>=', now()->subMinute())
                        ->first();

                    if (!$existingTransaction) {
                        // Log stock transaction
                        ItemStockTransaction::create([
                            'item_id' => $item->id,
                            'user_id' => $user->id,
                            'type' => 'out',
                            'quantity' => $quantity,
                            'remarks' => "Order #{$order->id} - Customer order fulfillment",
                        ]);
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $item->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $price * $quantity,
                        'is_backorder' => false,
                    ]);
                    continue;
                }

                // Partial fulfillment
                if ($available > 0) {
                    $fulfilledQty = $available;
                    $fulfilledSubtotal = $price * $fulfilledQty;
                    $item->stock = 0;
                    $item->save();

                    // Check for duplicate transaction to prevent duplicates
                    // Look for existing transaction with same order, item, quantity within last minute
                    $existingTransaction = ItemStockTransaction::where('item_id', $item->id)
                        ->where('type', 'out')
                        ->where('quantity', $fulfilledQty)
                        ->where('remarks', "Order #{$order->id} - Partial fulfillment (customer order)")
                        ->where('created_at', '>=', now()->subMinute())
                        ->first();

                    if (!$existingTransaction) {
                        // Log stock transaction
                        ItemStockTransaction::create([
                            'item_id' => $item->id,
                            'user_id' => $user->id,
                            'type' => 'out',
                            'quantity' => $fulfilledQty,
                            'remarks' => "Order #{$order->id} - Partial fulfillment (customer order)",
                        ]);
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $item->id,
                        'quantity' => $fulfilledQty,
                        'price' => $price,
                        'subtotal' => $fulfilledSubtotal,
                        'is_backorder' => false,
                    ]);
                }

                $backQty = $quantity - $available;
                if ($backQty > 0) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $item->id,
                        'quantity' => $backQty,
                        'price' => $price,
                        'subtotal' => $price * $backQty,
                        'is_backorder' => true,
                        'backorder_status' => \App\Models\OrderItem::BO_PENDING,
                    ]);
                }
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


