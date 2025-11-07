<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CustomCartItem;
use App\Models\CustomOrder;
use App\Models\Item;
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
        $cart = Cart::where('status', 'active')
            ->where('user_id', $user->id ?? null)
            ->orWhere(function ($q) {
                $q->where('status', 'active')->whereNull('user_id')->where('session_id', session()->getId());
            })
            ->first();

        $items = collect();
        $customItems = collect();
        $subtotal = 0.0;
        if ($cart) {
            $items = CartItem::with('item.photos')->where('cart_id', $cart->id)->get();
            $customItems = CustomCartItem::where('session_id', $cart->session_id)->get();
            $subtotal = (float) $items->sum('subtotal');
        }

        return view('checkout', [
            'user' => $user,
            'cartItems' => $items,
            'customCartItems' => $customItems,
            'subtotal' => $subtotal,
            'shipping' => 0.0,
            'total' => $subtotal,
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
            'payment_method' => 'required|in:COD,GCash,Card,Bank',
            'order_type' => 'nullable|in:standard,backorder,custom',
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
        $customCartItems = CustomCartItem::where('session_id', $cart->session_id)->get();
        if ($cartItems->isEmpty() && $customCartItems->isEmpty()) {
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
                if (($validated['order_type'] ?? null) === 'custom') {
                    $inferredType = 'custom';
                } elseif ($hasBack) {
                    $inferredType = 'backorder';
                }

                $orderStatus = match ($inferredType) {
                    'standard' => 'processing',
                    'backorder' => 'backorder',
                    'custom' => 'pending',
                    default => 'pending',
                };

                $order = Order::create([
                    'user_id' => $user?->id,
                    'order_type' => $inferredType,
                    'status' => $orderStatus,
                    'total_amount' => $total,
                    'payment_method' => $validated['payment_method'] === 'Bank' ? 'Card' : $validated['payment_method'],
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

                // Save custom-order requests (no price until reviewed)
                foreach ($customCartItems as $cci) {
                    $order->customOrders()->create([
                        'custom_name' => $cci->custom_name,
                        'description' => $cci->description,
                        'customization_details' => $cci->customization_details,
                        'reference_image_path' => $cci->reference_image_path,
                        'quantity' => $cci->quantity,
                        'price_estimate' => null,
                        'status' => \App\Models\CustomOrder::STATUS_PENDING_REVIEW,
                    ]);
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

                // Clear cart (regular and custom) and mark converted
                $cart->items()->delete();
                CustomCartItem::where('session_id', $cart->session_id)->delete();
                $cart->status = 'converted';
                $cart->save();

                return $order;
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['checkout' => $e->getMessage()])->withInput();
        }

        return redirect()->route('customer.orders.show', $order->id)->with('success', 'Order placed successfully');
    }
}


