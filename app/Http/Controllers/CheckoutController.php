<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
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
        $subtotal = 0.0;
        if ($cart) {
            $items = CartItem::with('item.photos')->where('cart_id', $cart->id)->get();
            $subtotal = (float) $items->sum('subtotal');
        }

        return view('checkout', [
            'user' => $user,
            'cartItems' => $items,
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
            'order_type' => 'nullable|in:standard,backorder,preorder,custom',
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
                $hasPre = false;
                $hasBack = false;
                foreach ($cartItems as $ci) {
                    $hasPre = $hasPre || (bool) $ci->item->isPreorder();
                    $hasBack = $hasBack || (bool) $ci->item->isBackorder();
                }
                if (($validated['order_type'] ?? null) === 'custom') {
                    $inferredType = 'custom';
                } elseif ($hasPre) {
                    $inferredType = 'preorder';
                } elseif ($hasBack) {
                    $inferredType = 'backorder';
                }

                // Validate preorder rule: must have release_date in the future
                if ($inferredType === 'preorder') {
                    foreach ($cartItems as $ci) {
                        if ($ci->item->isPreorder()) {
                            $rd = $ci->item->release_date;
                            if (!$rd || !$rd->isFuture()) {
                                throw new \RuntimeException('This item is not available for preorder: '.$ci->item->name);
                            }
                        }
                    }
                }

                $orderStatus = match ($inferredType) {
                    'standard' => 'processing',
                    'backorder' => 'backorder',
                    'preorder' => 'preorder',
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

                    // Standard: deduct stock now
                    if ($inferredType === 'standard' && !($item->isPreorder() || $item->isBackorder())) {
                        if ($item->stock < $ci->quantity) {
                            throw new \RuntimeException('Not enough stock for '.$item->name);
                        }
                        $item->stock -= $ci->quantity;
                        $item->save();
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $item->id,
                        'quantity' => $ci->quantity,
                        'price' => $ci->price,
                        'subtotal' => $ci->subtotal,
                        'is_preorder' => $item->isPreorder(),
                        'is_backorder' => $item->isBackorder(),
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

                // Clear cart
                $cart->items()->delete();
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


