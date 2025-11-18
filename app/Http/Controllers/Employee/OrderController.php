<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Item;
use App\Models\OrderItem;
use App\Models\ItemStockTransaction;
use App\Models\User;
use App\Models\Address;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
	{
        $query = Order::query()
			->select('orders.*') // ensure unique orders across filters that may translate to joins/exists
			->distinct()
			->with(['user', 'items.item', 'childOrders', 'cancellationRequests', 'returnRequests'])
			->latest();

        // Filter by order type
        $type = $request->string('type')->toString();
    if (in_array($type, ['standard', 'backorder', 'custom', 'completed', 'cancelled'], true)) {
            if ($type === 'completed') {
                $query->where('status', 'completed');
            } elseif ($type === 'cancelled') {
                $query->where('status', 'cancelled');
            } else {
                $query->where('order_type', $type);
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        // Filter by backorder item status (search orders that have items with the given backorder_status)
        if ($request->filled('backorder_status')) {
            $bs = $request->string('backorder_status')->toString();
            $query->whereHas('items', function ($q) use ($bs) {
                $q->where('is_backorder', true)->where('backorder_status', $bs);
            });
        }

        // Filter by date range
        if ($request->filled('from') || $request->filled('to')) {
            if ($request->filled('from')) {
                $query->whereDate('created_at', '>=', $request->string('from')->toString());
            }
            if ($request->filled('to')) {
                $query->whereDate('created_at', '<=', $request->string('to')->toString());
            }
        }

        // Search by order id, customer name or email
        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            // Remove # symbol if present for ID search
            $idQuery = ltrim($q, '#');
            $query->where(function ($sub) use ($q, $idQuery) {
                $sub->where('id', $idQuery)
                    ->orWhere('id', 'like', "%$idQuery%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('name', 'like', "%$q%")
                          ->orWhere('email', 'like', "%$q%");
                    })
                    ->orWhere('status', 'like', "%$q%");
            });
        }

        // Exclude child orders from main list (they'll be shown under parent)
        $query->whereNull('parent_order_id');

        $orders = $query->paginate(15)->withQueryString();

        return view('employee.orders', [
            'orders' => $orders,
            'activeType' => $type,
        ]);
    }    public function show($id)
    {
        $order = Order::with([
            'user.address',
            'items.item.photos',
            'customOrders',
            'childOrders',
            'parentOrder',
            'payments.verifier',
            'finalPaymentVerifier',
            'cancellationRequests.handledBy',
            'returnRequests',
        ])->findOrFail($id);
        // If the request expects JSON (modal usage), return JSON; otherwise render a full details page
        if (request()->expectsJson()) {
            return response()->json($order);
        }
        return view('employee.order-show', compact('order'));
    }

    /**
     * Update backorder status for a single order item.
     */
    public function updateItemBackorder($orderId, $itemId, Request $request)
    {
        $validated = $request->validate([
            'backorder_status' => 'required|in:pending_stock,in_progress,fulfilled',
            'expected_restock_date' => 'nullable|date',
        ]);

        $order = Order::with('items')->findOrFail($orderId);
        $oi = $order->items()->where('id', $itemId)->firstOrFail();

        $old = $oi->backorder_status;
        $oi->backorder_status = $validated['backorder_status'];
        $oi->save();

        // If status moved to in_progress or fulfilled, notify customer
        if ($old !== $oi->backorder_status && in_array($oi->backorder_status, [\App\Models\OrderItem::BO_IN_PROGRESS, \App\Models\OrderItem::BO_FULFILLED], true)) {
            try {
                $oi->loadMissing('order.user', 'item');
                if ($oi->order && $oi->order->user && $oi->order->user->email) {
                    \Mail::to($oi->order->user->email)->send(new \App\Mail\BackorderReady($oi));
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to send backorder notification', ['error' => $e->getMessage(), 'order_item' => $oi->id]);
            }
        }

        // Optionally update order-level expected_restock_date
        if (!empty($validated['expected_restock_date'])) {
            $order->expected_restock_date = $validated['expected_restock_date'];
            $order->save();
        }

        return response()->json(['success' => true, 'item' => $oi]);
    }

    public function update($id, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,ready_to_ship,shipped,delivered,completed,cancelled,backorder,in_design,in_production,ready_for_delivery',
            'tracking_number' => 'nullable|string|max:100',
            'delivered_at' => 'nullable|date',
        ]);
        
        $order = Order::with(['payments', 'childOrders.payments', 'items.item'])->findOrFail($id);
        
        // Validate forward-only status transition
        if (!$order->canTransitionTo($validated['status'])) {
            $validNextStatuses = $order->getValidNextStatuses();
            $nextStatusesList = !empty($validNextStatuses) ? implode(', ', array_map('ucwords', array_map(fn($s) => str_replace('_', ' ', $s), $validNextStatuses))) : 'None (order is completed or cancelled)';
            return back()->withErrors(['status' => "Invalid status transition. Current status: " . ucwords(str_replace('_', ' ', $order->status)) . ". Valid next statuses: {$nextStatusesList}."]);
        }
        
        // For back orders: Validate stock availability and reduce stock when transitioning to "ready_to_ship" (Preparing to Ship)
        if ($order->order_type === 'backorder' && $validated['status'] === 'ready_to_ship' && $order->status === 'processing') {
            // Check if all back order items have sufficient stock
            $insufficientStock = [];
            foreach ($order->items as $orderItem) {
                if ($orderItem->is_backorder) {
                    $item = $orderItem->item;
                    $requiredQty = (int) $orderItem->quantity;
                    $availableStock = (int) max(0, $item->stock ?? 0);
                    
                    if ($availableStock < $requiredQty) {
                        $insufficientStock[] = [
                            'item' => $item->name,
                            'required' => $requiredQty,
                            'available' => $availableStock,
                        ];
                    }
                }
            }
            
            if (!empty($insufficientStock)) {
                $errorMessage = "Cannot transition to 'Preparing to Ship'. Insufficient stock for the following items:\n";
                foreach ($insufficientStock as $item) {
                    $errorMessage .= "- {$item['item']}: Required {$item['required']}, Available {$item['available']}\n";
                }
                return back()->withErrors(['status' => trim($errorMessage)]);
            }
            
            // Reduce stock for all backorder items when transitioning to "ready_to_ship"
            foreach ($order->items as $orderItem) {
                if ($orderItem->is_backorder) {
                    $item = Item::where('id', $orderItem->item_id)->lockForUpdate()->first();
                    if ($item) {
                        $requiredQty = (int) $orderItem->quantity;
                        $availableStock = (int) max(0, $item->stock ?? 0);
                        
                        if ($availableStock >= $requiredQty) {
                            $item->stock = $availableStock - $requiredQty;
                            $item->save();
                            
                            // Log stock transaction
                            ItemStockTransaction::create([
                                'item_id' => $item->id,
                                'user_id' => Auth::id(),
                                'type' => 'out',
                                'quantity' => $requiredQty,
                                'remarks' => "Backorder #{$order->id} - Status changed to 'Preparing to Ship'",
                            ]);
                            
                            // Update order item backorder status to fulfilled
                            $orderItem->backorder_status = \App\Models\OrderItem::BO_FULFILLED;
                            $orderItem->save();
                        }
                    }
                }
            }
        }
        
        // Check if this is a COD order
        $isCod = $order->payment_method === 'COD';
        
        // For COD orders: Allow processing without payment, but block completion until payment is collected
        if ($isCod) {
            // Block completion if COD payment hasn't been collected
            if ($validated['status'] === 'completed' && $order->payment_status === 'pending_cod') {
                return back()->withErrors(['payment' => 'COD payment must be collected before the order can be marked as completed. Please mark COD as collected first.']);
            }
            // Allow all other status changes for COD orders (including processing, shipped, etc.)
        } else {
            // For 50% upfront orders: Block completion until final payment is verified
            if ($validated['status'] === 'completed' && $order->requiresFinalPaymentVerification()) {
                if (!$order->hasFinalPaymentVerified()) {
                    return back()->withErrors(['payment' => 'Final payment verification is required before the order can be marked as completed. Please verify the final payment collected by the courier first.']);
                }
            }
            // For non-COD orders, validate payment before processing
            $processingStatuses = ['processing', 'ready_to_ship', 'shipped', 'delivered', 'in_design', 'in_production', 'ready_for_delivery', 'completed'];
            if (in_array($validated['status'], $processingStatuses)) {
                if (!$order->hasVerifiedPayment()) {
                    if ($order->hasPendingPaymentVerification()) {
                        return back()->withErrors(['payment' => 'Payment verification is pending. Please verify the payment before processing this order.']);
                    }
                    
                    // Check if payment was rejected
                    $latestPayment = $order->getLatestPayment();
                    if ($latestPayment && $latestPayment->isRejected()) {
                        return back()->withErrors(['payment' => 'Payment was rejected. Cannot process order with rejected payment.']);
                    }
                    
                    return back()->withErrors(['payment' => 'Payment must be verified before the order can be processed.']);
                }
            }
        }
        
        $order->status = $validated['status'];
        
        // Save shipping fields if provided
        // Only save tracking number if it's not empty (trim whitespace)
        if (isset($validated['tracking_number'])) {
            $trackingNumber = trim($validated['tracking_number']);
            // Validate that tracking number is not empty if provided
            if ($trackingNumber === '') {
                return back()->withErrors(['tracking_number' => 'Tracking number cannot be empty. Please enter a valid tracking number or leave it blank.']);
            }
            $order->tracking_number = $trackingNumber;
        }
        
        // Automatically set carrier to LBC for all orders
        $order->carrier = 'lbc';
        if (isset($validated['delivered_at'])) {
            $order->delivered_at = $validated['delivered_at'];
        }
        
        // Auto-set delivered_at when status is marked as 'delivered'
        if ($validated['status'] === 'delivered' && !$order->delivered_at) {
            $order->delivered_at = now();
        }
        
        $order->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'order' => $order]);
        }
        return back()->with('success', 'Order updated');
    }

    public function destroy($id, Request $request)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Order deleted');
    }

    /**
     * Store a manually-created order by an employee.
     */
    public function store(Request $request)
    {
        // Custom validation: either user_id OR new customer fields must be provided
        $request->validate([
            'order_type' => 'required|in:standard,backorder,custom',
            'user_id' => 'nullable|exists:users,id',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:1000',
            'contact_number' => 'nullable|string|max:50|regex:/^(\+639|09)\d{9}$/',
            'email' => 'nullable|email|max:255',
            'payment_method' => 'nullable|in:COD,GCASH,BANK,NONE',
            'paid' => 'nullable|boolean',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_phone' => 'nullable|string|max:50',
            'shipping_fee' => 'nullable|numeric|min:0',
            // Standard/Backorder items
            'items' => 'required_if:order_type,standard,backorder|array|min:1',
            'items.*.item_id' => 'required_if:order_type,standard,backorder|integer|exists:items,id',
            'items.*.quantity' => 'required_if:order_type,standard,backorder|integer|min:1',
            // Custom order fields
            'custom_name' => 'required_if:order_type,custom|string|max:255',
            'custom_description' => 'required_if:order_type,custom|string',
            'custom_quantity' => 'required_if:order_type,custom|integer|min:1',
            'custom_quotation' => 'required_if:order_type,custom|numeric|min:0',
            'custom_reference_images' => 'required_if:order_type,custom|array|min:1|max:4',
            'custom_reference_images.*' => 'required_if:order_type,custom|image|mimes:jpeg,png,jpg|max:5120',
            'estimated_completion_date' => 'nullable|date',
            'expected_restock_date' => 'nullable|date',
            'payment_status' => 'required|in:unpaid,partially_paid,paid,pending_cod',
        ], [
            'order_type.required' => 'Please select an order type.',
            'order_type.in' => 'Invalid order type selected.',
            'first_name.required_without' => 'First name is required when entering new customer information.',
            'last_name.required_without' => 'Last name is required when entering new customer information.',
            'address.required_without' => 'Address is required when entering new customer information.',
            'contact_number.required_without' => 'Contact number is required when entering new customer information.',
            'contact_number.regex' => 'Contact number must be in format 09XXXXXXXXX or +639XXXXXXXXX.',
            'email.required_without' => 'Email is required when entering new customer information.',
            'email.email' => 'Please enter a valid email address.',
            'items.required_if' => 'Please add at least one item to the order.',
            'items.*.item_id.exists' => 'One of the selected items does not exist.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'custom_name.required_if' => 'Product name is required for custom orders.',
            'custom_description.required_if' => 'Description is required for custom orders.',
            'custom_quantity.required_if' => 'Quantity is required for custom orders.',
            'custom_quotation.required_if' => 'Quotation amount is required for custom orders.',
            'custom_quotation.min' => 'Quotation amount must be greater than or equal to 0.',
            'custom_reference_images.required_if' => 'At least one reference image is required for custom orders.',
            'custom_reference_images.max' => 'Maximum 4 reference images allowed.',
            'payment_status.required' => 'Payment status is required.',
            'payment_status.in' => 'Invalid payment status selected.',
        ]);

        // Custom validation: Either user_id must be provided OR all new customer fields must be provided
        $userId = $request->input('user_id');
        $hasNewCustomerFields = !empty($request->input('first_name')) && 
                                !empty($request->input('last_name')) && 
                                !empty($request->input('email')) && 
                                !empty($request->input('contact_number')) && 
                                !empty($request->input('address'));
        
        if (empty($userId) && !$hasNewCustomerFields) {
            return back()->withInput()->withErrors([
                'user_id' => 'Please either select an existing customer or enter new customer information.',
                'first_name' => 'Customer information is required.',
            ]);
        }
        
        if (!empty($userId) && $hasNewCustomerFields) {
            return back()->withInput()->withErrors([
                'user_id' => 'Please either select an existing customer OR enter new customer information, not both.',
            ]);
        }

        // Now validate the request properly with conditional requirements
        $validationRules = [
            'order_type' => 'required|in:standard,backorder,custom',
            'user_id' => 'nullable|exists:users,id',
            'payment_method' => 'nullable|in:COD,GCASH,BANK,NONE',
            'paid' => 'nullable|boolean',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_phone' => 'nullable|string|max:50',
            'shipping_fee' => 'nullable|numeric|min:0',
            'items' => 'required_if:order_type,standard,backorder|array|min:1',
            'items.*.item_id' => 'required_if:order_type,standard,backorder|integer|exists:items,id',
            'items.*.quantity' => 'required_if:order_type,standard,backorder|integer|min:1',
            'custom_name' => 'required_if:order_type,custom|string|max:255',
            'custom_description' => 'required_if:order_type,custom|string',
            'custom_quantity' => 'required_if:order_type,custom|integer|min:1',
            'custom_quotation' => 'required_if:order_type,custom|numeric|min:0',
            'custom_reference_images' => 'required_if:order_type,custom|array|min:1|max:4',
            'custom_reference_images.*' => 'required_if:order_type,custom|image|mimes:jpeg,png,jpg|max:5120',
            'estimated_completion_date' => 'nullable|date',
            'expected_restock_date' => 'nullable|date',
            'payment_status' => 'required|in:unpaid,partially_paid,paid,pending_cod',
        ];
        
        // Add conditional validation for customer fields
        if (empty($userId)) {
            $validationRules['first_name'] = 'required|string|max:100';
            $validationRules['last_name'] = 'required|string|max:100';
            $validationRules['address'] = 'required|string|max:1000';
            $validationRules['contact_number'] = 'required|string|max:50|regex:/^(\+639|09)\d{9}$/';
            $validationRules['email'] = 'required|email|max:255';
        } else {
            $validationRules['first_name'] = 'nullable|string|max:100';
            $validationRules['last_name'] = 'nullable|string|max:100';
            $validationRules['address'] = 'nullable|string|max:1000';
            $validationRules['contact_number'] = 'nullable|string|max:50|regex:/^(\+639|09)\d{9}$/';
            $validationRules['email'] = 'nullable|email|max:255';
        }
        
        // Log the validation rules for debugging malformed regex issues
        try {
            \Log::debug('Employee Order validation rules', ['rules' => $validationRules]);
        } catch (\Throwable $e) {
            // ignore logging errors
        }

        $validated = $request->validate($validationRules, [
            'order_type.required' => 'Please select an order type.',
            'order_type.in' => 'Invalid order type selected.',
            'first_name.required' => 'First name is required when entering new customer information.',
            'last_name.required' => 'Last name is required when entering new customer information.',
            'address.required' => 'Address is required when entering new customer information.',
            'contact_number.required' => 'Contact number is required when entering new customer information.',
            'contact_number.regex' => 'Contact number must be in format 09XXXXXXXXX or +639XXXXXXXXX.',
            'email.required' => 'Email is required when entering new customer information.',
            'email.email' => 'Please enter a valid email address.',
            'items.required_if' => 'Please add at least one item to the order.',
            'items.*.item_id.exists' => 'One of the selected items does not exist.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'custom_name.required_if' => 'Product name is required for custom orders.',
            'custom_description.required_if' => 'Description is required for custom orders.',
            'custom_quantity.required_if' => 'Quantity is required for custom orders.',
            'custom_quotation.required_if' => 'Quotation amount is required for custom orders.',
            'custom_quotation.min' => 'Quotation amount must be greater than or equal to 0.',
            'custom_reference_images.required_if' => 'At least one reference image is required for custom orders.',
            'custom_reference_images.max' => 'Maximum 4 reference images allowed.',
            'payment_status.required' => 'Payment status is required.',
            'payment_status.in' => 'Invalid payment status selected.',
        ]);

        // Validate payment method for backorder and custom orders
        $orderType = $validated['order_type'];
        if (in_array($orderType, ['backorder', 'custom']) && $validated['payment_method'] === 'COD') {
            return back()->withInput()->withErrors(['payment_method' => 'COD is not available for Back Orders and Custom Orders. Please use Bank Transfer or GCash.']);
        }

        // Validate payment status based on order type
        $paymentStatus = $validated['payment_status'];
        if ($orderType === 'standard' && $paymentStatus === 'partially_paid') {
            return back()->withInput()->withErrors(['payment_status' => 'Standard orders cannot be partially paid. Please select Fully Paid or Pending Payment.']);
        }
        if (in_array($orderType, ['backorder', 'custom']) && $paymentStatus === 'pending_cod') {
            return back()->withInput()->withErrors(['payment_status' => 'COD is not available for Back Orders and Custom Orders.']);
        }

        try {
            DB::transaction(function () use ($validated, $request, $orderType, &$order) {
                // Handle Custom Order
                if ($orderType === 'custom') {
                    // Handle multiple file uploads for custom order
                    $imagePaths = [];
                    if ($request->hasFile('custom_reference_images')) {
                        foreach ($request->file('custom_reference_images') as $file) {
                            $path = $file->store('custom-orders', 'public');
                            $imagePaths[] = $path;
                        }
                    }

                    // Store first image in reference_image_path for backward compatibility
                    $firstImagePath = !empty($imagePaths) ? $imagePaths[0] : null;

                    // Store all image paths in customization_details
                    $customizationDetails = [
                        'images' => $imagePaths,
                    ];

                    // Get or create user
                    $userId = $validated['user_id'] ?? null;
                    if (empty($userId)) {
                        $guestEmail = $validated['email'] ?? ('guest_' . time() . '_' . Str::random(6) . '@example.local');
                        $newUser = User::create([
                            'first_name' => $validated['first_name'],
                            'last_name' => $validated['last_name'],
                            'email' => $guestEmail,
                            'password' => Hash::make(Str::random(12)),
                            'role' => 'customer',
                            'status' => 'active',
                        ]);

                        if (!empty($validated['address'])) {
                            try {
                                Address::create([
                                    'user_id' => $newUser->id,
                                    'type' => 'shipping',
                                    'address_line' => $validated['address'],
                                    'phone_number' => $validated['contact_number'] ?? null,
                                ]);
                            } catch (\Throwable $e) {
                                // ignore address creation errors
                            }
                        }

                        $userId = $newUser->id;
                    }

                    // Get quotation amount
                    $quotationAmount = (float) ($validated['custom_quotation'] ?? 0);
                    $requiredPayment = $quotationAmount * 0.5;
                    $remainingBalance = $quotationAmount * 0.5;
                    
                    // Determine payment status
                    $paymentStatus = $validated['payment_status'] ?? 'unpaid';
                    if ($paymentStatus === 'partially_paid') {
                        $remainingBalance = $quotationAmount * 0.5;
                    } elseif ($paymentStatus === 'paid') {
                        $remainingBalance = 0;
                    }

                    // Create custom order
                    $order = Order::create([
                        'user_id' => $userId,
                        'order_type' => Order::TYPE_CUSTOM,
                        'status' => Order::STATUS_PENDING,
                        'total_amount' => $quotationAmount,
                        'required_payment_amount' => $requiredPayment,
                        'remaining_balance' => $remainingBalance,
                        'payment_method' => $validated['payment_method'] ?? 'NONE',
                        'payment_status' => $paymentStatus,
                        'recipient_name' => $validated['recipient_name'] ?? ($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
                        'recipient_phone' => $validated['recipient_phone'] ?? ($validated['contact_number'] ?? null),
                        'shipping_fee' => $validated['shipping_fee'] ?? 0,
                    ]);

                    // Create custom order record
                    $customOrder = \App\Models\CustomOrder::create([
                        'order_id' => $order->id,
                        'custom_name' => $validated['custom_name'],
                        'description' => $validated['custom_description'],
                        'customization_details' => $customizationDetails,
                        'reference_image_path' => $firstImagePath,
                        'quantity' => (int) $validated['custom_quantity'],
                        'price_estimate' => $quotationAmount,
                        'status' => \App\Models\CustomOrder::STATUS_APPROVED, // Auto-approved when created by employee with quotation
                        'estimated_completion_date' => !empty($validated['estimated_completion_date']) ? $validated['estimated_completion_date'] : null,
                    ]);

                    // If payment status is partially_paid, create payment record
                    if ($paymentStatus === 'partially_paid') {
                        Payment::create([
                            'order_id' => $order->id,
                            'method' => strtolower($validated['payment_method'] ?? 'cash'),
                            'amount' => $requiredPayment,
                            'status' => 'paid',
                            'verification_status' => 'approved',
                            'verified_by' => Auth::id(),
                            'verified_at' => now(),
                        ]);
                    } elseif ($paymentStatus === 'paid') {
                        Payment::create([
                            'order_id' => $order->id,
                            'method' => strtolower($validated['payment_method'] ?? 'cash'),
                            'amount' => $quotationAmount,
                            'status' => 'paid',
                            'verification_status' => 'approved',
                            'verified_by' => Auth::id(),
                            'verified_at' => now(),
                        ]);
                    }

                    return; // Exit early for custom orders
                }

                // Handle Standard and Backorder orders
                $itemsInput = $validated['items'] ?? [];

                // Prepare totals and backorder detection
                $total = 0.0;
                $hasBackorder = false;
                $hasStandard = false;
                $preparedItems = [];

                foreach ($itemsInput as $it) {
                    $item = Item::findOrFail($it['item_id']);
                    $qty = (int) $it['quantity'];
                    $price = (float) ($item->price ?? $item->unit_price ?? 0);
                    $subtotal = $price * $qty;
                    $is_backorder = false;
                    
                    // For backorder type, all items are backorders
                    // For standard type, check stock availability
                    if ($orderType === 'backorder') {
                        $is_backorder = true;
                        $hasBackorder = true;
                    } elseif (($item->stock ?? 0) < $qty) {
                        $is_backorder = true;
                        $hasBackorder = true;
                    } else {
                        $hasStandard = true;
                    }
                    $total += $subtotal;
                    $preparedItems[] = compact('item', 'qty', 'price', 'subtotal', 'is_backorder');
                }

                // Determine final order_type
                if ($orderType === 'backorder') {
                    $finalOrderType = Order::TYPE_BACKORDER;
                } elseif ($hasBackorder && $hasStandard) {
                    $finalOrderType = Order::TYPE_MIXED;
                } else {
                    $finalOrderType = Order::TYPE_STANDARD;
                }

                // Payment status logic - use payment_status from form
                $paymentMethod = $validated['payment_method'] ?? 'NONE';
                $paymentStatus = $validated['payment_status'] ?? 'unpaid';
                
                // Validate payment status based on order type
                if ($finalOrderType === Order::TYPE_BACKORDER && $paymentStatus === 'pending_cod') {
                    $paymentStatus = 'unpaid'; // COD not allowed for backorders
                }

                // If user_id not provided, create a guest customer record
                $userId = $validated['user_id'] ?? null;
                if (empty($userId)) {
                    $guestEmail = $validated['email'] ?? ('guest_' . time() . '_' . Str::random(6) . '@example.local');
                    $newUser = User::create([
                        'first_name' => $validated['first_name'],
                        'last_name' => $validated['last_name'],
                        'email' => $guestEmail,
                        'password' => Hash::make(Str::random(12)),
                        'role' => 'customer',
                        'status' => 'active',
                    ]);

                    // Create an address record if provided
                    if (!empty($validated['address'])) {
                        try {
                            Address::create([
                                'user_id' => $newUser->id,
                                'type' => 'shipping',
                                'address_line' => $validated['address'],
                                'phone_number' => $validated['contact_number'] ?? null,
                            ]);
                        } catch (\Throwable $e) {
                            // ignore address creation errors for now
                        }
                    }

                    $userId = $newUser->id;
                }

                // Determine initial status
                $initialStatus = Order::STATUS_PROCESSING;
                if ($finalOrderType === Order::TYPE_BACKORDER) {
                    $initialStatus = Order::STATUS_PROCESSING; // "Awaiting Stock"
                }

                // Calculate required payment and remaining balance
                $requiredPayment = 0;
                $remainingBalance = 0;
                
                if ($finalOrderType === Order::TYPE_BACKORDER) {
                    $requiredPayment = $total * 0.5;
                    if ($paymentStatus === 'partially_paid') {
                        $remainingBalance = $total * 0.5;
                    } elseif ($paymentStatus === 'paid') {
                        $remainingBalance = 0;
                    } else {
                        $remainingBalance = $total * 0.5;
                    }
                } elseif ($finalOrderType === Order::TYPE_STANDARD) {
                    $requiredPayment = $total;
                    $remainingBalance = 0;
                } elseif ($finalOrderType === Order::TYPE_MIXED) {
                    // For mixed orders, calculate based on standard and backorder portions
                    $standardTotal = 0;
                    $backorderTotal = 0;
                    foreach ($preparedItems as $pi) {
                        if ($pi['is_backorder']) {
                            $backorderTotal += $pi['subtotal'];
                        } else {
                            $standardTotal += $pi['subtotal'];
                        }
                    }
                    $requiredPayment = $standardTotal + ($backorderTotal * 0.5);
                    if ($paymentStatus === 'partially_paid') {
                        $remainingBalance = $backorderTotal * 0.5;
                    } elseif ($paymentStatus === 'paid') {
                        $remainingBalance = 0;
                    } else {
                        $remainingBalance = $backorderTotal * 0.5;
                    }
                }

                // Create order
                $order = Order::create([
                    'user_id' => $userId,
                    'order_type' => $finalOrderType,
                    'status' => $initialStatus,
                    'total_amount' => $total,
                    'required_payment_amount' => $requiredPayment,
                    'remaining_balance' => $remainingBalance,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentStatus,
                    'recipient_name' => $validated['recipient_name'] ?? ($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
                    'recipient_phone' => $validated['recipient_phone'] ?? ($validated['contact_number'] ?? null),
                    'shipping_fee' => $validated['shipping_fee'] ?? 0,
                    'expected_restock_date' => !empty($validated['expected_restock_date']) ? $validated['expected_restock_date'] : null,
                ]);

                // Create order items and deduct stock for standard items
                foreach ($preparedItems as $pi) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $pi['item']->id,
                        'quantity' => $pi['qty'],
                        'price' => $pi['price'],
                        'subtotal' => $pi['subtotal'],
                        'is_backorder' => $pi['is_backorder'],
                        'backorder_status' => $pi['is_backorder'] ? \App\Models\OrderItem::BO_PENDING : null,
                    ]);

                    // If not a backorder, deduct stock immediately and create transaction
                    if (!$pi['is_backorder']) {
                        $item = Item::where('id', $pi['item']->id)->lockForUpdate()->first();
                        $available = max(0, (int) ($item->stock ?? 0));
                        $required = $pi['qty'];
                        if ($available < $required) {
                            throw new \Exception("Insufficient stock for item {$item->name}");
                        }
                        $item->stock = $available - $required;
                        $item->save();

                        // Create stock transaction (out)
                        ItemStockTransaction::create([
                            'item_id' => $item->id,
                            'user_id' => auth()->id() ?? null,
                            'type' => 'out',
                            'quantity' => $required,
                            'remarks' => "Order #{$order->id} - Employee created",
                        ]);
                    }
                }

                // Create payment records if payment was received
                if ($paymentStatus === 'partially_paid' || $paymentStatus === 'paid') {
                    $paymentAmount = ($paymentStatus === 'partially_paid') ? $order->required_payment_amount : $order->total_amount;
                    Payment::create([
                        'order_id' => $order->id,
                        'method' => strtolower($paymentMethod === 'NONE' ? 'cash' : $paymentMethod),
                        'amount' => $paymentAmount,
                        'status' => 'paid',
                        'verification_status' => 'approved',
                        'verified_by' => Auth::id(),
                        'verified_at' => now(),
                    ]);
                } elseif ($paymentStatus === 'pending_cod') {
                    Payment::create([
                        'order_id' => $order->id,
                        'method' => 'cod',
                        'amount' => $order->total_amount,
                        'status' => 'pending',
                        'verification_status' => 'pending',
                    ]);
                }
            });

            return redirect()->route('employee.orders', ['type' => ''])->with('success', 'Order created successfully!');
        } catch (\Exception $e) {
            \Log::error('Employee order creation failed', ['error' => $e->getMessage(), 'input' => $request->all()]);
            return back()->withInput()->withErrors(['error' => 'Failed to create order: ' . $e->getMessage()]);
        }
    }

    /**
     * Verify payment for an order
     */
    public function verifyPayment($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can verify payments');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'verification_notes' => 'required_if:action,reject|nullable|string|max:500',
        ], [
            'action.required' => 'Please select an action (approve or reject)',
            'action.in' => 'Invalid action. Must be approve or reject',
            'verification_notes.required_if' => 'Rejection reason is required when rejecting payment',
            'verification_notes.max' => 'Rejection reason must not exceed 500 characters',
        ]);

        $order = Order::with(['payments', 'childOrders.payments'])->findOrFail($id);

        // Get the latest payment(s) to verify
        $paymentsToVerify = [];
        if ($order->order_type === 'mixed' && $order->childOrders()->exists()) {
            // For mixed orders, verify payments for all child orders
            foreach ($order->childOrders as $child) {
                $latestPayment = $child->payments()->latest()->first();
                if ($latestPayment && $latestPayment->isPendingVerification()) {
                    $paymentsToVerify[] = $latestPayment;
                }
            }
        } else {
            // Single order
            $latestPayment = $order->payments()->latest()->first();
            if (!$latestPayment) {
                return back()->withErrors(['payment' => 'No payment found for this order']);
            }
            if (!$latestPayment->isPendingVerification()) {
                return back()->withErrors(['payment' => 'This payment is not pending verification']);
            }
            $paymentsToVerify[] = $latestPayment;
        }

        if (empty($paymentsToVerify)) {
            return back()->withErrors(['payment' => 'No payments pending verification for this order']);
        }

        try {
            \DB::transaction(function () use ($validated, $paymentsToVerify, $order, $user) {
                $action = $validated['action'];
                $notes = $validated['verification_notes'] ?? null;

                foreach ($paymentsToVerify as $payment) {
                    $payment->verified_by = $user->id;
                    $payment->verification_status = $action === 'approve' ? 'approved' : 'rejected';
                    $payment->verification_notes = $notes;
                    $payment->verified_at = now();
                    $payment->save();

                    // If approved, update payment status to paid
                    if ($action === 'approve') {
                        $payment->status = 'paid';
                        $payment->save();

                        // Update the order's payment status
                        $paymentOrder = $payment->order;
                        $requiredAmount = (float) ($paymentOrder->required_payment_amount ?? $paymentOrder->calculateRequiredPaymentAmount());
                        $paidAmount = (float) $payment->amount;

                        if ($paidAmount >= $paymentOrder->total_amount) {
                            $paymentOrder->payment_status = 'paid';
                            $paymentOrder->remaining_balance = 0;
                            
                            // For custom orders: automatically set status to "in_design" when payment is verified
                            // Only update if order is still pending (prevents status regression)
                            if ($paymentOrder->order_type === Order::TYPE_CUSTOM) {
                                // Only set to in_design if status is pending (not already in a later stage)
                                if ($paymentOrder->status === Order::STATUS_PENDING) {
                                    $paymentOrder->status = 'in_design';
                                }
                            } elseif ($paymentOrder->order_type !== Order::TYPE_CUSTOM && $paymentOrder->status === Order::STATUS_PENDING) {
                                // For non-custom orders, set to processing if still pending
                                $paymentOrder->status = Order::STATUS_PROCESSING;
                            }
                        } else {
                            $paymentOrder->payment_status = 'partially_paid';
                            $paymentOrder->remaining_balance = max(0, $paymentOrder->total_amount - $paidAmount);
                            // For partial payments, set to backorder status (only for non-custom orders)
                            if ($paymentOrder->order_type !== Order::TYPE_CUSTOM && $paymentOrder->status === Order::STATUS_PENDING) {
                                $paymentOrder->status = Order::STATUS_BACKORDER;
                            }
                        }
                        $paymentOrder->save();
                    } else {
                        // Rejected
                        $paymentOrder = $payment->order;
                        $paymentOrder->payment_status = 'payment_rejected';
                        $paymentOrder->save();
                    }
                }

                // For mixed orders, update parent order status
                if ($order->order_type === 'mixed' && $order->childOrders()->exists()) {
                    $allVerified = true;
                    $allRejected = true;
                    $hasRejected = false;

                    foreach ($order->childOrders as $child) {
                        $childPayment = $child->payments()->latest()->first();
                        if ($childPayment) {
                            if ($childPayment->isVerified()) {
                                $allRejected = false;
                            } else if ($childPayment->isRejected()) {
                                $hasRejected = true;
                                $allVerified = false;
                            } else {
                                $allVerified = false;
                                $allRejected = false;
                            }
                        } else {
                            $allVerified = false;
                        }
                    }

                    if ($allVerified) {
                        $order->payment_status = 'paid';
                        // Set order status to processing if it's still pending
                        if ($order->status === Order::STATUS_PENDING) {
                            $order->status = Order::STATUS_PROCESSING;
                        }
                    } else if ($hasRejected) {
                        $order->payment_status = 'payment_rejected';
                    }
                    $order->save();
                }
            });

            $message = $validated['action'] === 'approve' 
                ? 'Payment verified and approved successfully' 
                : 'Payment rejected. Customer will be notified.';

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Payment verification failed', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Payment verification failed: ' . $e->getMessage()], 500);
            }

            return back()->withErrors(['payment' => 'Payment verification failed. Please try again.']);
        }
    }

    /**
     * Verify final payment for 50% upfront orders
     */
    public function verifyFinalPayment($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can verify final payments');
        }

        $order = Order::with(['childOrders', 'finalPaymentVerifier'])->findOrFail($id);

        // Validate that this order requires final payment verification
        if (!$order->requiresFinalPaymentVerification()) {
            return back()->withErrors(['payment' => 'This order does not require final payment verification.']);
        }

        // Check if already verified
        if ($order->hasFinalPaymentVerified()) {
            return back()->withErrors(['payment' => 'Final payment has already been verified for this order.']);
        }

        // Check if there's a remaining balance to verify
        if ($order->remaining_balance <= 0) {
            return back()->withErrors(['payment' => 'No remaining balance to verify.']);
        }

        try {
            \DB::transaction(function () use ($order, $user) {
                // For mixed orders, verify all child orders that require it
                if ($order->order_type === 'mixed' && $order->childOrders()->exists()) {
                    foreach ($order->childOrders as $child) {
                        if ($child->requiresFinalPaymentVerification() && !$child->final_payment_verified && $child->remaining_balance > 0) {
                            $child->final_payment_verified = true;
                            $child->final_payment_verified_at = now();
                            $child->final_payment_verified_by = $user->id;
                            
                            // For backorder sub-orders: Update payment status to "paid" when final payment is verified
                            if ($child->order_type === 'backorder') {
                                $child->payment_status = 'paid';
                                $child->remaining_balance = 0;
                            }
                            
                            $child->save();
                        }
                    }
                } else {
                    // Single order
                    $order->final_payment_verified = true;
                    $order->final_payment_verified_at = now();
                    $order->final_payment_verified_by = $user->id;
                    
                    // For backorders: Update payment status to "paid" when final payment is verified
                    if ($order->order_type === 'backorder') {
                        $order->payment_status = 'paid';
                        $order->remaining_balance = 0;
                    }
                    
                    $order->save();
                }
            });

            $message = 'Final payment verified successfully. Order can now be marked as completed.';

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Final payment verification failed', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Final payment verification failed: ' . $e->getMessage()], 500);
            }

            return back()->withErrors(['payment' => 'Final payment verification failed. Please try again.']);
        }
    }

    /**
     * Mark COD payment as collected
     */
    public function collectCod($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'Only employees can mark COD as collected');
        }

        $order = Order::with(['childOrders'])->findOrFail($id);

        // Validate that this is a COD order
        $isCod = $order->payment_method === 'COD';
        if (!$isCod) {
            return back()->withErrors(['payment' => 'This order is not a COD order.']);
        }

        if ($order->payment_status !== 'pending_cod') {
            return back()->withErrors(['payment' => 'This COD order has already been processed.']);
        }

        try {
            \DB::transaction(function () use ($order) {
                // Update order payment status to paid
                $order->payment_status = 'paid';
                
                // Note: Order status is not automatically changed - employee can update it as needed
                // This allows flexibility if order is already in processing/shipped/etc.
                
                $order->save();

                // For mixed orders, update child orders
                if ($order->order_type === 'mixed' && $order->childOrders()->exists()) {
                    foreach ($order->childOrders as $child) {
                        if ($child->payment_status === 'pending_cod') {
                            $child->payment_status = 'paid';
                            // Note: Child order status is not automatically changed
                            $child->save();
                        }
                    }
                }
            });

            $message = 'COD payment marked as collected. Order can now be marked as completed.';

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('COD collection failed', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'COD collection failed: ' . $e->getMessage()], 500);
            }

            return back()->withErrors(['payment' => 'COD collection failed. Please try again.']);
        }
    }
}


