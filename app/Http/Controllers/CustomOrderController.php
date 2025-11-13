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
            'reference_images' => 'required|array|min:1|max:4',
            'reference_images.*' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'quantity' => 'required|integer|min:1',
        ]);

        // Handle multiple file uploads
        $imagePaths = [];
        if ($request->hasFile('reference_images')) {
            foreach ($request->file('reference_images') as $file) {
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

        // Create order
        $order = Order::create([
            'user_id' => Auth::id(),
            'order_type' => Order::TYPE_CUSTOM,
            'status' => Order::STATUS_PENDING,
            'total_amount' => 0, // Will be set after review
            'payment_status' => 'unpaid',
        ]);

        // Create custom order
        $customOrder = new CustomOrder([
            'custom_name' => $validated['custom_name'],
            'description' => $validated['description'],
            'customization_details' => $customizationDetails,
            'reference_image_path' => $firstImagePath,
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

        // Redirect to unified order details page
        return redirect()->route('customer.orders.show', $customOrder->order->id);
    }
}