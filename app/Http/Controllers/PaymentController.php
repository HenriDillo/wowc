<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

		// Validate payment amount against required payment
		$paymentAmount = (float) $validated['amount'];
		$requiredAmount = (float) ($order->required_payment_amount ?? $order->total_amount);
		
		if ($paymentAmount < $requiredAmount) {
			return response()->json([
				'success' => false,
				'message' => "Payment amount ₱" . number_format($paymentAmount, 2) . " is below the required amount of ₱" . number_format($requiredAmount, 2)
			], 422);
		}

		try {
			$results = DB::transaction(function () use ($order, $paymentAmount, $validated) {
				$createdPayments = [];
				$reference = $validated['reference'];
				// If this is a parent mixed order, allocate payment to children in order (standard first)
				if ($order->order_type === 'mixed' && $order->childOrders()->exists()) {
					$remaining = $paymentAmount;
					// Load children and prefer standard before backorder
					$children = $order->childOrders()->get()->sortByDesc(function ($c) {
						return $c->order_type === 'standard' ? 1 : 0;
					});
					foreach ($children as $child) {
						$childRequired = (float) ($child->required_payment_amount ?? $child->total_amount * $child->getRequiredPaymentPercentage());
						if ($remaining <= 0) {
							// no money left to allocate
							continue;
						}
						if ($remaining >= $childRequired) {
							// fully pay this child
							$payAmount = $childRequired;
							$createdPayments[] = Payment::create([
								'order_id' => $child->id,
								'method' => 'gcash',
								'amount' => $payAmount,
								'status' => 'paid',
								'transaction_id' => $reference,
							]);
							$child->payment_method = 'GCash';
							$child->payment_status = 'paid';
							$child->remaining_balance = 0;
							$child->save();
							$remaining -= $payAmount;
						} else {
							// partial payment for this child
							$payAmount = $remaining;
							$createdPayments[] = Payment::create([
								'order_id' => $child->id,
								'method' => 'gcash',
								'amount' => $payAmount,
								'status' => 'paid',
								'transaction_id' => $reference,
							]);
							$child->payment_method = 'GCash';
							$child->payment_status = 'partially_paid';
							$child->remaining_balance = max(0, $childRequired - $payAmount);
							$child->save();
							$remaining = 0;
						}
					}
					// Update parent aggregate
					$parentRemaining = $order->childOrders()->sum('remaining_balance');
					$order->payment_method = 'GCash';
					$order->remaining_balance = $parentRemaining;
					$order->payment_status = $parentRemaining <= 0 ? 'paid' : 'partially_paid';
					$order->status = $order->payment_status === 'paid' ? Order::STATUS_PROCESSING : Order::STATUS_BACKORDER;
					$order->save();
					return $createdPayments;
				}
				// Non-parent (single) order handling
				$payment = Payment::create([
					'order_id' => $order->id,
					'method' => 'gcash',
					'amount' => $paymentAmount,
					'status' => 'paid',
					'transaction_id' => $validated['reference'],
				]);
				$createdPayments[] = $payment;
				// decide order payment status based on required payment amount
				$orderTotal = (float) $order->total_amount;
				$orderRequired = (float) ($order->required_payment_amount ?? $orderTotal);
				// For backorder/custom: remaining balance is based on FULL total, not required amount
				$remainingBalance = max(0, $orderTotal - $orderRequired); // The remaining portion after down payment
				
				if ($paymentAmount >= $orderTotal) {
					// Paid everything
					$order->payment_status = 'paid';
					$order->remaining_balance = 0;
					$order->status = Order::STATUS_PROCESSING;
				} else {
					// Partial payment (at this point, validation ensures $paymentAmount >= $orderRequired)
					$order->payment_status = 'partially_paid';
					$order->remaining_balance = $remainingBalance;
					$order->status = Order::STATUS_BACKORDER;
				}
				$order->payment_method = 'GCash';
				$order->save();
				// If this is a child order, update siblings/parent appropriately
				if ($order->parent_order_id) {
					$parent = Order::find($order->parent_order_id);
					if ($parent) {
						$parentRemaining = $parent->childOrders()->sum('remaining_balance');
						$parent->remaining_balance = $parentRemaining;
						$parent->payment_status = $parentRemaining <= 0 ? 'paid' : 'partially_paid';
						$parent->payment_method = 'GCash';
						$parent->status = $parent->payment_status === 'paid' ? Order::STATUS_PROCESSING : Order::STATUS_BACKORDER;
						$parent->save();
					}
				}
				return $createdPayments;
			});

			return response()->json(['success' => true, 'payments' => $results]);
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

		// Validate payment amount against required payment
		$paymentAmount = (float) $validated['amount'];
		$requiredAmount = (float) ($order->required_payment_amount ?? $order->total_amount);
		
		if ($paymentAmount < $requiredAmount) {
			return response()->json([
				'success' => false,
				'message' => "Payment amount ₱" . number_format($paymentAmount, 2) . " is below the required amount of ₱" . number_format($requiredAmount, 2)
			], 422);
		}

		try {
			$results = DB::transaction(function () use ($order, $paymentAmount, $request) {
				$path = $request->file('proof')->store('bank-proofs', 'public');
				$createdPayments = [];
				// If parent mixed order, allocate pending payments to children
				if ($order->order_type === 'mixed' && $order->childOrders()->exists()) {
					$remaining = $paymentAmount;
					$children = $order->childOrders()->get()->sortByDesc(function ($c) {
						return $c->order_type === 'standard' ? 1 : 0;
					});
					foreach ($children as $child) {
						$childRequired = (float) ($child->required_payment_amount ?? $child->total_amount * $child->getRequiredPaymentPercentage());
						if ($remaining <= 0) continue;
						$alloc = min($remaining, $childRequired);
						$createdPayments[] = Payment::create([
							'order_id' => $child->id,
							'method' => 'bank',
							'amount' => $alloc,
							'status' => 'pending_verification',
							'proof_image' => $path,
						]);
						// leave child.order payment_status as unpaid until verification, but store a tentative remaining_balance
						$child->remaining_balance = max(0, $childRequired - $alloc);
						$child->payment_method = 'Bank Transfer';
						$child->save();
						$remaining -= $alloc;
					}
					// parent stays unpaid awaiting verification, but record aggregate values
					$order->payment_method = 'Bank Transfer';
					$order->save();
					return ['payments' => $createdPayments, 'path' => $path];
				}
				// Single order path
				$path = $request->file('proof')->store('bank-proofs', 'public');
				$payment = Payment::create([
					'order_id' => $order->id,
					'method' => 'bank',
					'amount' => $paymentAmount,
					'status' => 'pending_verification',
					'proof_image' => $path,
				]);
				$order->payment_method = 'Bank Transfer';
				// keep unpaid until admin verifies
				$order->payment_status = 'unpaid';
				$order->save();
				return ['payments' => [$payment], 'path' => $path];
			});

			return response()->json(['success' => true, 'result' => $results, 'proof_url' => Storage::url($results['path'])]);
		} catch (\Exception $e) {
			return response()->json(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()], 500);
		}
	}
}


