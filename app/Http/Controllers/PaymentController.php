<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
	public function confirmGCash(Request $request)
	{
		$user = Auth::user();
		if (!$user) {
			return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
		}

		$validated = $request->validate([
			'order_id' => 'required|exists:orders,id',
			'amount' => 'required|numeric|min:0',
			'reference' => 'required|string|min:6|max:64',
		]);

		$order = Order::findOrFail($validated['order_id']);

		// Ensure user owns this order
		if ($order->user_id !== $user->id) {
			return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
		}

		try {
			$payment = Payment::create([
				'order_id' => $order->id,
				'method' => 'gcash',
				'amount' => $validated['amount'],
				'status' => 'paid',
				'transaction_id' => $validated['reference'],
			]);

			// Mirror to order for compatibility and mark as paid
			$order->payment_method = 'GCash';
			$order->payment_status = 'paid';
			
			// If order is still pending or waiting for payment, move to processing/backorder
			if ($order->status === 'pending') {
				// Determine appropriate status based on order type
				if ($order->order_type === Order::TYPE_BACKORDER) {
					$order->status = Order::STATUS_BACKORDER;
				} else {
					$order->status = Order::STATUS_PROCESSING;
				}
			}
			
			$order->save();

			// If this is a custom order awaiting payment, move to in_production
			if ($order->customOrders && $order->customOrders()->where('status', 'approved')->exists()) {
				$order->customOrders()->where('status', 'approved')->update(['status' => 'in_production']);
				$order->status = Order::STATUS_PROCESSING;
				$order->save();
			}

			return response()->json(['success' => true, 'payment' => $payment]);
		} catch (\Exception $e) {
			return response()->json(['success' => false, 'message' => 'Payment processing failed: ' . $e->getMessage()], 500);
		}
	}

	public function uploadBankProof(Request $request)
	{
		$user = Auth::user();
		if (!$user) {
			return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
		}

		$validated = $request->validate([
			'order_id' => 'required|exists:orders,id',
			'amount' => 'required|numeric|min:0',
			'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
		]);

		$order = Order::findOrFail($validated['order_id']);

		// Ensure user owns this order
		if ($order->user_id !== $user->id) {
			return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
		}

		try {
			$path = $request->file('proof')->store('bank-proofs', 'public');

			$payment = Payment::create([
				'order_id' => $order->id,
				'method' => 'bank',
				'amount' => $validated['amount'],
				'status' => 'pending_verification',
				'proof_image' => $path,
			]);

			$order->payment_method = 'Bank Transfer';
			// Keep payment_status as unpaid until admin verifies the bank transfer
			$order->payment_status = 'unpaid';
			
			// Bank transfer requires verification, but we'll mark as awaiting verification
			// Admin will verify and update status to 'paid' after which order processing begins
			
			$order->save();

			return response()->json(['success' => true, 'payment' => $payment, 'proof_url' => Storage::url($path)]);
		} catch (\Exception $e) {
			return response()->json(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()], 500);
		}
	}
}


