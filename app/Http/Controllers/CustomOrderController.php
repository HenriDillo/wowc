<?php

namespace App\Http\Controllers;

use App\Models\CustomOrder;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CustomOrderController extends Controller
{
    public function create()
    {
        return view('custom-order.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'custom_name' => 'required|string|max:255',
            'description' => 'required|string',
            'customization_details' => 'required|array',
            'customization_details.dimensions' => 'required|string',
            'reference_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'quantity' => 'required|integer|min:1',
        ]);

        // Handle file upload
        if ($request->hasFile('reference_image')) {
            $path = $request->file('reference_image')->store('custom-orders', 'public');
            $validated['reference_image_path'] = $path;
        }

        // Create order
        $order = Order::create([
            'user_id' => Auth::id(),
            'order_type' => Order::TYPE_CUSTOM,
            'status' => Order::STATUS_PENDING,
            'total_amount' => 0, // Will be set after review
        ]);

        // Create custom order
        $customOrder = new CustomOrder([
            'custom_name' => $validated['custom_name'],
            'description' => $validated['description'],
            'customization_details' => $validated['customization_details'],
            'reference_image_path' => $validated['reference_image_path'] ?? null,
            'quantity' => $validated['quantity'],
            'status' => CustomOrder::STATUS_PENDING_REVIEW,
        ]);

        $order->customOrders()->save($customOrder);

        return redirect()->route('customer.orders.show', $order->id)
            ->with('success', 'Custom order submitted successfully! We will review your request and contact you soon.');
    }

    public function show($id)
    {
        $customOrder = CustomOrder::with('order')->findOrFail($id);
        
        if ($customOrder->order->user_id !== Auth::id()) {
            abort(403);
        }

        return view('custom-order.show', compact('customOrder'));
    }
}