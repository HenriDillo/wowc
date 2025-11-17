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
use App\Models\Payment;
use App\Services\LbcShippingCalculator;
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
		$customOrder = null;
		if (request()->filled('order_id')) {
			$orderId = request('order_id');
			
			// Validate order ID format
			if (!is_numeric($orderId) || $orderId <= 0) {
				return redirect()->route('customer.orders.index')
					->with('error', 'Invalid order ID. Please try again.');
			}
			
			// Load order with relationships
			$payOrder = \App\Models\Order::with(['user', 'customOrders', 'payments'])
				->where('id', $orderId)
				->when($user, fn($q) => $q->where('user_id', $user->id))
				->first();
			
			// Check if order exists
			if (!$payOrder) {
				return redirect()->route('customer.orders.index')
					->with('error', 'Order not found. The order may not exist or you may not have permission to access it.');
			}
			
			// Check if order is fully paid
			if ($payOrder->isFullyPaid()) {
				return redirect()->route('customer.orders.show', $payOrder->id)
					->with('info', 'This order is already fully paid. No payment is required.');
			}
			
			// For custom orders, validate custom order data
			if ($payOrder->order_type === 'custom') {
				$customOrder = $payOrder->customOrders->first();
				
				if (!$customOrder) {
					return redirect()->route('customer.orders.index')
						->with('error', 'Custom order details not found. Please contact support.');
				}
				
				// Validate that custom order has a price set
				if (!$customOrder->price_estimate || $customOrder->price_estimate <= 0) {
					return redirect()->route('customer.orders.show', $payOrder->id)
						->with('error', 'Custom order price has not been set yet. Please wait for employee review.');
				}
				
				// Ensure custom order is approved
				if ($customOrder->status !== \App\Models\CustomOrder::STATUS_APPROVED) {
					return redirect()->route('customer.orders.show', $payOrder->id)
						->with('error', 'This custom order is not yet approved for payment.');
				}
			}
		}

		$cart = Cart::where('status', 'active')
            ->where('user_id', $user->id ?? null)
            ->orWhere(function ($q) {
                $q->where('status', 'active')->whereNull('user_id')->where('session_id', session()->getId());
            })
            ->first();

		$items = collect();
        $standardItems = collect();
        $backorderItems = collect();
        $standardSubtotal = 0.0;
        $backorderSubtotal = 0.0;
        $subtotal = 0.0;
        $isMixedOrder = false;
        $requiredPaymentAmount = 0.0;
        
		if ($cart && !$payOrder) {
            $items = CartItem::with('item.photos')->where('cart_id', $cart->id)->get();
            
            // Separate standard and backorder items
            $standardItems = $items->filter(fn($ci) => !($ci->is_backorder ?? false) && !optional($ci->item)->isBackorder());
            $backorderItems = $items->filter(fn($ci) => ($ci->is_backorder ?? false) || optional($ci->item)->isBackorder());
            
            $standardSubtotal = (float) $standardItems->sum('subtotal');
            $backorderSubtotal = (float) $backorderItems->sum('subtotal');
            $subtotal = $standardSubtotal + $backorderSubtotal;
            
            // Check if this is a mixed order
            $isMixedOrder = $standardItems->isNotEmpty() && $backorderItems->isNotEmpty();
            
            // Calculate required payment: 100% of standard + 50% of backorder
            $requiredPaymentAmount = $standardSubtotal + ($backorderSubtotal * 0.5);
            
        } elseif ($payOrder) {
            // For existing orders (custom order payment)
            $subtotal = (float) $payOrder->total_amount;
            $requiredPaymentAmount = $payOrder->calculateRequiredPaymentAmount();
        }

		return view('checkout', [
            'user' => $user,
            'cartItems' => $items,
            'standardItems' => $standardItems,
            'backorderItems' => $backorderItems,
            'standardSubtotal' => $standardSubtotal,
            'backorderSubtotal' => $backorderSubtotal,
            'subtotal' => $subtotal,
            'shipping' => 0.0,
			'total' => $payOrder ? (float) $payOrder->total_amount : $subtotal,
            'isMixedOrder' => $isMixedOrder,
            'requiredPaymentAmount' => $requiredPaymentAmount,
			'payOrder' => $payOrder,
			'customOrder' => $customOrder,
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
            // Accept common variants for bank transfer method to remain compatible with frontend values
            'payment_method' => 'required|in:GCash,Bank,Bank Transfer,COD',
			'order_type' => 'nullable|in:standard,backorder',
        ]);
        
        $cart = Cart::where('status', 'active')
            ->where(function ($q) use ($user) {
                if ($user) {
                    $q->where('user_id', $user->id);
                } else {
                    $q->whereNull('user_id')->where('session_id', session()->getId());
                }
            })->first();
        
        if ($cart) {
            $cartItems = CartItem::with('item')->where('cart_id', $cart->id)->get();
            $standardItems = $cartItems->filter(fn($ci) => !($ci->is_backorder ?? false) && !($ci->item->isBackorder() ?? false));
            $backorderItems = $cartItems->filter(fn($ci) => ($ci->is_backorder ?? false) || ($ci->item->isBackorder() ?? false));
            $isMixedOrder = $standardItems->isNotEmpty() && $backorderItems->isNotEmpty();
            
            // Check if this requires 50% upfront (Backorder, Custom Order, or Mixed Order with Standard + Backorder)
            $requires50PercentUpfront = $backorderItems->isNotEmpty() || $isMixedOrder;
            
            // Reject COD for 50% upfront orders
            if ($requires50PercentUpfront && $validated['payment_method'] === 'COD') {
                return back()->withErrors(['payment_method' => 'COD is not available for Backorder, Custom Order, or Mixed Order (Standard + Backorder). Please use Bank Transfer or GCash.'])->withInput();
            }
        }
        
        // Get cart again for order creation (or use the one we already have)
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

                // Separate standard and backorder items
                $standardItems = $cartItems->filter(fn($ci) => !($ci->is_backorder ?? false) && !($ci->item->isBackorder() ?? false));
                $backorderItems = $cartItems->filter(fn($ci) => ($ci->is_backorder ?? false) || ($ci->item->isBackorder() ?? false));
                
                $standardSubtotal = (float) $standardItems->sum('subtotal');
                $backorderSubtotal = (float) $backorderItems->sum('subtotal');
                
                // Check if this is a mixed order
                $isMixedOrder = $standardItems->isNotEmpty() && $backorderItems->isNotEmpty();
                
                // Calculate COD shipping fees if payment method is COD
                $shippingFee = 0.0;
                $codFee = 0.0;
                $isCod = $validated['payment_method'] === 'COD';
                
                if ($isCod) {
                    // Estimate weight from cart items
                    $estimatedWeight = LbcShippingCalculator::estimateWeight($cartItems);
                    
                    // Calculate shipping fee
                    $shippingFee = LbcShippingCalculator::calculateShippingFee(
                        $estimatedWeight,
                        $validated['province'],
                        $validated['city']
                    );
                    
                    // Calculate COD fee based on order amount (before shipping)
                    $codFee = LbcShippingCalculator::calculateCodFee($subtotal);
                    
                    // Add shipping and COD fees to total
                    $total += $shippingFee + $codFee;
                }

                // Determine payment method and status
                // Normalize payment method to match database enum values
                $paymentMethod = match($validated['payment_method']) {
                    'Bank' => 'Bank Transfer',
                    'COD' => 'COD',
                    default => $validated['payment_method'], // GCash stays as is
                };
                $paymentStatus = $isCod ? 'pending_cod' : 'unpaid';
                
                // Create parent order if mixed, or single order if not
                $parentOrder = null;
                if ($isMixedOrder) {
                    $parentOrder = Order::create([
                        'user_id' => $user?->id,
                        'order_type' => 'mixed',
                        'status' => 'pending',
                        'total_amount' => $total,
                        'required_payment_amount' => $isCod ? $total : ($standardSubtotal + ($backorderSubtotal * 0.5)),
                        'remaining_balance' => $isCod ? 0 : ($backorderSubtotal * 0.5),
                        'payment_method' => $paymentMethod,
                        'payment_status' => $paymentStatus,
                        'recipient_name' => $isCod ? trim($validated['first_name'] . ' ' . $validated['last_name']) : null,
                        'recipient_phone' => $isCod ? $validated['phone_number'] : null,
                        'shipping_fee' => $isCod ? $shippingFee : 0,
                        'cod_fee' => $isCod ? $codFee : 0,
                        'carrier' => 'lbc', // Automatically set to LBC
                    ]);
                }

                // Create standard order
                $standardOrder = null;
                if ($standardItems->isNotEmpty()) {
                    // For COD, calculate fees proportionally for standard items
                    $standardShippingFee = $isCod ? ($shippingFee * ($standardSubtotal / $subtotal)) : 0;
                    $standardCodFee = $isCod ? ($codFee * ($standardSubtotal / $subtotal)) : 0;
                    $standardTotal = $standardSubtotal + $standardShippingFee + $standardCodFee;
                    
                    $standardOrder = Order::create([
                        'user_id' => $user?->id,
                        'parent_order_id' => $isMixedOrder ? $parentOrder->id : null,
                        'order_type' => 'standard',
                        'status' => 'pending',
                        'total_amount' => $standardTotal,
                        'required_payment_amount' => $isCod ? $standardTotal : $standardSubtotal,
                        'remaining_balance' => 0,
                        'payment_method' => $paymentMethod,
                        'payment_status' => $paymentStatus,
                        'recipient_name' => $isCod ? trim($validated['first_name'] . ' ' . $validated['last_name']) : null,
                        'recipient_phone' => $isCod ? $validated['phone_number'] : null,
                        'shipping_fee' => $standardShippingFee,
                        'cod_fee' => $standardCodFee,
                        'carrier' => 'lbc', // Automatically set to LBC
                    ]);

                    // Add standard items to standard order
                    foreach ($standardItems as $ci) {
                        $item = Item::lockForUpdate()->find($ci->item_id);
                        if (!$item) {
                            throw new \RuntimeException('Item not found');
                        }

                        $requestedQty = (int) $ci->quantity;
                        $available = (int) max(0, $item->stock ?? 0);

                        if ($available >= $requestedQty) {
                            $item->stock = $available - $requestedQty;
                            $item->save();

                            // Check for duplicate transaction to prevent duplicates
                            // Look for existing transaction with same order, item, quantity within last minute
                            $existingTransaction = ItemStockTransaction::where('item_id', $item->id)
                                ->where('type', 'out')
                                ->where('quantity', $requestedQty)
                                ->where('remarks', "Order #{$standardOrder->id} - Customer order fulfillment")
                                ->where('created_at', '>=', now()->subMinute())
                                ->first();

                            if (!$existingTransaction) {
                                ItemStockTransaction::create([
                                    'item_id' => $item->id,
                                    'user_id' => $user?->id,
                                    'type' => 'out',
                                    'quantity' => $requestedQty,
                                    'remarks' => "Order #{$standardOrder->id} - Customer order fulfillment",
                                ]);
                            }

                            OrderItem::create([
                                'order_id' => $standardOrder->id,
                                'item_id' => $item->id,
                                'quantity' => $requestedQty,
                                'price' => $ci->price,
                                'subtotal' => $ci->subtotal,
                                'is_backorder' => false,
                                'backorder_status' => null,
                            ]);
                        } else if ($available > 0) {
                            // Partial stock available - fulfill what we can for standard order
                            $fulfilledQty = $available;
                            $fulfilledSubtotal = $ci->price * $fulfilledQty;
                            $item->stock = 0;
                            $item->save();

                            // Check for duplicate transaction to prevent duplicates
                            // Look for existing transaction with same order, item, quantity within last minute
                            $existingTransaction = ItemStockTransaction::where('item_id', $item->id)
                                ->where('type', 'out')
                                ->where('quantity', $fulfilledQty)
                                ->where('remarks', "Order #{$standardOrder->id} - Partial fulfillment")
                                ->where('created_at', '>=', now()->subMinute())
                                ->first();

                            if (!$existingTransaction) {
                                ItemStockTransaction::create([
                                    'item_id' => $item->id,
                                    'user_id' => $user?->id,
                                    'type' => 'out',
                                    'quantity' => $fulfilledQty,
                                    'remarks' => "Order #{$standardOrder->id} - Partial fulfillment",
                                ]);
                            }

                            OrderItem::create([
                                'order_id' => $standardOrder->id,
                                'item_id' => $item->id,
                                'quantity' => $fulfilledQty,
                                'price' => $ci->price,
                                'subtotal' => $fulfilledSubtotal,
                                'is_backorder' => false,
                                'backorder_status' => null,
                            ]);

                            // Remainder goes to backorder order
                            $backQty = $requestedQty - $fulfilledQty;
                            if ($backQty > 0) {
                                $backSubtotal = $ci->price * $backQty;
                                // Will be added to backorder order below
                                $ci->setAttribute('backorder_quantity', $backQty);
                                $ci->setAttribute('backorder_subtotal', $backSubtotal);
                            }
                        } else {
                            // No stock available - will be handled in backorder
                            $ci->setAttribute('backorder_quantity', $requestedQty);
                            $ci->setAttribute('backorder_subtotal', $ci->subtotal);
                        }
                    }
                }

                // Create backorder order
                $backorderOrder = null;
                if ($backorderItems->isNotEmpty()) {
                    // For COD, calculate fees proportionally for backorder items
                    $backorderShippingFee = $isCod ? ($shippingFee * ($backorderSubtotal / $subtotal)) : 0;
                    $backorderCodFee = $isCod ? ($codFee * ($backorderSubtotal / $subtotal)) : 0;
                    $backorderTotal = $backorderSubtotal + $backorderShippingFee + $backorderCodFee;
                    
                    $backorderOrder = Order::create([
                        'user_id' => $user?->id,
                        'parent_order_id' => $isMixedOrder ? $parentOrder->id : null,
                        'order_type' => 'backorder',
                        'status' => 'processing', // Automatically set to "Awaiting Stock" for back orders
                        'total_amount' => $backorderTotal,
                        'required_payment_amount' => $isCod ? $backorderTotal : ($backorderSubtotal * 0.5),
                        'remaining_balance' => $isCod ? 0 : ($backorderSubtotal * 0.5),
                        'payment_method' => $paymentMethod,
                        'payment_status' => $paymentStatus,
                        'recipient_name' => $isCod ? trim($validated['first_name'] . ' ' . $validated['last_name']) : null,
                        'recipient_phone' => $isCod ? $validated['phone_number'] : null,
                        'shipping_fee' => $backorderShippingFee,
                        'cod_fee' => $backorderCodFee,
                        'carrier' => 'lbc', // Automatically set to LBC
                    ]);

                    // Add backorder items to backorder order
                    foreach ($backorderItems as $ci) {
                        OrderItem::create([
                            'order_id' => $backorderOrder->id,
                            'item_id' => $ci->item_id,
                            'quantity' => $ci->quantity,
                            'price' => $ci->price,
                            'subtotal' => $ci->subtotal,
                            'is_backorder' => true,
                            'backorder_status' => \App\Models\OrderItem::BO_PENDING,
                        ]);
                    }

                    // Also add partial stock items that belong to backorder
                    foreach ($cartItems as $ci) {
                        if (isset($ci->backorder_quantity) && $ci->backorder_quantity > 0) {
                            OrderItem::create([
                                'order_id' => $backorderOrder->id,
                                'item_id' => $ci->item_id,
                                'quantity' => $ci->backorder_quantity,
                                'price' => $ci->price,
                                'subtotal' => $ci->backorder_subtotal,
                                'is_backorder' => true,
                                'backorder_status' => \App\Models\OrderItem::BO_PENDING,
                            ]);
                        }
                    }
                }

                // Create Payment records for COD orders
                if ($isCod) {
                    // For COD, create payment records for each order
                    if ($standardOrder) {
                        Payment::create([
                            'order_id' => $standardOrder->id,
                            'method' => 'cod',
                            'amount' => $standardOrder->total_amount,
                            'status' => 'pending',
                            'verification_status' => 'pending',
                        ]);
                    }
                    
                    if ($backorderOrder) {
                        Payment::create([
                            'order_id' => $backorderOrder->id,
                            'method' => 'cod',
                            'amount' => $backorderOrder->total_amount,
                            'status' => 'pending',
                            'verification_status' => 'pending',
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

                // Return the primary order (parent if mixed, standard if not mixed with backorder, etc)
                return $parentOrder ?? $standardOrder ?? $backorderOrder;
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


