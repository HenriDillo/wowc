<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CustomOrder;
use App\Models\Item;
use App\Models\ItemStockTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function page()
    {
        $user = Auth::user();
        if ($user) {
            $user->loadMissing('address');
        }
		// Optional: paying an existing order (e.g., confirmed custom order)
		$payOrder = null;
		if (request()->filled('order_id')) {
			$payOrder = \App\Models\Order::with(['user', 'customOrders'])->where('id', request('order_id'))->when($user, fn($q) => $q->where('user_id', $user->id))->first();
		}

		$cart = Cart::where('status', 'active')
            ->where('user_id', $user->id ?? null)
            ->orWhere(function ($q) {
                $q->where('status', 'active')->whereNull('user_id')->where('session_id', session()->getId());
            })
            ->first();

		$items = collect();
        $subtotal = 0.0;
		if ($cart && !$payOrder) {
            $items = CartItem::with('item.photos')->where('cart_id', $cart->id)->get();
            $subtotal = (float) $items->sum('subtotal');
        }

		return view('checkout', [
            'user' => $user,
            'cartItems' => $items,
            'subtotal' => $subtotal,
            'shipping' => 0.0,
			'total' => $payOrder ? (float) $payOrder->total_amount : $subtotal,
			'payOrder' => $payOrder,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
		$validated = $request->validate([
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'address_line' => 'required|string|max:255',
            'city' => 'required|string|max:120',
            'postal_code' => 'required|string|max:20',
            'province' => 'required|string|max:120',
            'phone_number' => 'required|string|max:30',
			'payment_method' => 'required|in:GCash,Bank',
			'order_type' => 'nullable|in:standard,backorder',
        ]);

        $cart = Cart::where('status', 'active')
            ->where(function ($q) use ($user) {
                if ($user) {
                    $q->where('user_id', $user->id);
                } else {
                    $q->whereNull('user_id')->where('session_id', session()->getId());
                }
            })->firstOrFail();

		$cartItems = CartItem::with('item')->where('cart_id', $cart->id)->get();
		if ($cartItems->isEmpty()) {
            return back()->withErrors(['cart' => 'Your cart is empty.']);
        }

        try {
            $order = DB::transaction(function () use ($user, $validated, $cartItems, $cart) {
                $subtotal = (float) $cartItems->sum('subtotal');
                $tax = 0.0; // extend later if tax rules apply
                $total = $subtotal + $tax;

                // Determine order type based on items or given input
                $inferredType = 'standard';
                $hasBack = false;
                foreach ($cartItems as $ci) {
                    // If the cart line is already marked as backorder, respect that; otherwise fall back to item's backorder flag
                    $hasBack = $hasBack || (bool) ($ci->is_backorder ?? $ci->item->isBackorder());
                }
				if ($hasBack) {
                    $inferredType = 'backorder';
                }

                // All orders start as pending until payment is confirmed
                $orderStatus = 'pending';

                $order = Order::create([
                    'user_id' => $user?->id,
                    'order_type' => $inferredType,
                    'status' => $orderStatus,
                    'total_amount' => $total,
					'payment_method' => null,
                    'payment_status' => 'unpaid',
                ]);

                foreach ($cartItems as $ci) {
                    $item = Item::lockForUpdate()->find($ci->item_id);
                    if (!$item) {
                        throw new \RuntimeException('Item not found');
                    }

                    $requestedQty = (int) $ci->quantity;
                    $available = (int) max(0, $item->stock ?? 0);

                    // If the cart line was already marked as backorder, create a backorder OrderItem and do not attempt to fulfill
                    if (!empty($ci->is_backorder)) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'item_id' => $item->id,
                            'quantity' => $requestedQty,
                            'price' => $ci->price,
                            'subtotal' => $ci->subtotal,
                            'is_backorder' => true,
                            'backorder_status' => \App\Models\OrderItem::BO_PENDING,
                        ]);
                        continue;
                    }

                    // If item is flagged as backorder-only, treat whole qty as backorder
                    if ($item->isBackorder()) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'item_id' => $item->id,
                            'quantity' => $requestedQty,
                            'price' => $ci->price,
                            'subtotal' => $ci->subtotal,
                            'is_backorder' => true,
                            'backorder_status' => \App\Models\OrderItem::BO_PENDING,
                        ]);
                        continue;
                    }

                    // If there's enough stock to fully fulfill the line
                    if ($available >= $requestedQty) {
                        $item->stock = $available - $requestedQty;
                        $item->save();

                        // Log stock transaction
                        ItemStockTransaction::create([
                            'item_id' => $item->id,
                            'user_id' => $user?->id,
                            'type' => 'out',
                            'quantity' => $requestedQty,
                            'remarks' => "Order #{$order->id} - Customer order fulfillment",
                        ]);

                        OrderItem::create([
                            'order_id' => $order->id,
                            'item_id' => $item->id,
                            'quantity' => $requestedQty,
                            'price' => $ci->price,
                            'subtotal' => $ci->subtotal,
                            'is_backorder' => false,
                            'backorder_status' => null,
                        ]);
                        continue;
                    }

                    // Partial or zero stock: create a fulfilled part (if any) and a backorder remainder
                    if ($available > 0) {
                        // Fulfilled portion
                        $fulfilledQty = $available;
                        $fulfilledSubtotal = $ci->price * $fulfilledQty;
                        $item->stock = 0;
                        $item->save();

                        // Log stock transaction
                        ItemStockTransaction::create([
                            'item_id' => $item->id,
                            'user_id' => $user?->id,
                            'type' => 'out',
                            'quantity' => $fulfilledQty,
                            'remarks' => "Order #{$order->id} - Partial fulfillment (customer order)",
                        ]);

                        OrderItem::create([
                            'order_id' => $order->id,
                            'item_id' => $item->id,
                            'quantity' => $fulfilledQty,
                            'price' => $ci->price,
                            'subtotal' => $fulfilledSubtotal,
                            'is_backorder' => false,
                            'backorder_status' => null,
                        ]);
                    }

                    $backQty = $requestedQty - $available;
                    if ($backQty > 0) {
                        $backSubtotal = $ci->price * $backQty;
                        OrderItem::create([
                            'order_id' => $order->id,
                            'item_id' => $item->id,
                            'quantity' => $backQty,
                            'price' => $ci->price,
                            'subtotal' => $backSubtotal,
                            'is_backorder' => true,
                            'backorder_status' => \App\Models\OrderItem::BO_PENDING,
                        ]);
                    }
                }

                // Upsert user's address as default shipping
                if ($user) {
                    $addr = Address::firstOrNew(['user_id' => $user->id, 'type' => 'shipping']);
                    $addr->fill([
                        'address_line' => $validated['address_line'],
                        'city' => $validated['city'],
                        'province' => $validated['province'],
                        'postal_code' => $validated['postal_code'],
                        'phone_number' => $validated['phone_number'],
                    ]);
                    $addr->save();
                }

				// Clear cart (regular) and mark converted
                $cart->items()->delete();
                $cart->status = 'converted';
                $cart->save();

                return $order;
            });
		} catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['checkout' => $e->getMessage()])->withInput();
        }

		if ($request->expectsJson()) {
			return response()->json(['success' => true, 'order_id' => $order->id, 'total' => (float) $order->total_amount]);
		}
		return redirect()->route('customer.orders.show', $order->id)->with('success', 'Order placed successfully');
    }
}


