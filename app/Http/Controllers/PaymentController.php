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
			'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
		], [
			'amount.required' => 'Please enter the payment amount.',
			'amount.numeric' => 'Payment amount must be a valid number.',
			'amount.min' => 'Payment amount must be greater than 0.',
			'reference.required' => 'Please enter the GCash reference number.',
			'reference.min' => 'Reference number must be at least 6 characters.',
			'proof.required' => 'Please upload payment proof.',
			'proof.image' => 'Payment proof must be an image file.',
			'proof.mimes' => 'Payment proof must be a JPEG, PNG, or JPG file.',
			'proof.max' => 'Payment proof file size must not exceed 5MB.',
		]);

		$order = Order::findOrFail($validated['order_id']);

		// Ensure user owns this order
		if ($order->user_id !== $user->id) {
			return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
		}

		// Check if order is already fully paid
		if ($order->isFullyPaid()) {
			return response()->json([
				'success' => false,
				'message' => 'This order is already fully paid. No additional payment is required.'
			], 422);
		}

		// Validate payment amount against required payment
		$paymentAmount = (float) $validated['amount'];
		$requiredAmount = (float) ($order->required_payment_amount ?? $order->total_amount);
		
		// Allow small tolerance for rounding (0.01)
		$tolerance = 0.01;
		if (abs($paymentAmount - $requiredAmount) > $tolerance) {
			return response()->json([
				'success' => false,
				'message' => "Please enter the correct payment amount. Required: ₱" . number_format($requiredAmount, 2) . ", Entered: ₱" . number_format($paymentAmount, 2)
			], 422);
		}

		try {
			$results = DB::transaction(function () use ($order, $paymentAmount, $validated, $request) {
				$createdPayments = [];
				$reference = $validated['reference'];
				
				// Store proof image for GCash
				$proofPath = null;
				if ($request->hasFile('proof')) {
					$proofPath = $request->file('proof')->store('gcash-proofs', 'public');
				}
				
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
								'status' => 'pending_verification',
								'verification_status' => 'pending',
								'transaction_id' => $reference,
								'proof_image' => $proofPath,
							]);
							$child->payment_method = 'GCash';
							$child->payment_status = 'pending_verification';
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
								'status' => 'pending_verification',
								'verification_status' => 'pending',
								'transaction_id' => $reference,
								'proof_image' => $proofPath,
							]);
							$child->payment_method = 'GCash';
							$child->payment_status = 'pending_verification';
							$child->remaining_balance = max(0, $childRequired - $payAmount);
							$child->save();
							$remaining = 0;
						}
					}
					// Update parent aggregate - keep as pending_verification until all children are verified
					$parentRemaining = $order->childOrders()->sum('remaining_balance');
					$order->payment_method = 'GCash';
					$order->remaining_balance = $parentRemaining;
					$order->payment_status = 'pending_verification';
					$order->save();
					return $createdPayments;
				}
				// Non-parent (single) order handling
				$payment = Payment::create([
					'order_id' => $order->id,
					'method' => 'gcash',
					'amount' => $paymentAmount,
					'status' => 'pending_verification',
					'verification_status' => 'pending',
					'transaction_id' => $validated['reference'],
					'proof_image' => $proofPath,
				]);
				$createdPayments[] = $payment;
				// decide order payment status based on required payment amount
				$orderTotal = (float) $order->total_amount;
				$orderRequired = (float) ($order->required_payment_amount ?? $orderTotal);
				// For backorder/custom: remaining balance is based on FULL total, not required amount
				$remainingBalance = max(0, $orderTotal - $orderRequired); // The remaining portion after down payment
				
				// Set payment status to pending_verification until employee verifies
				$order->payment_status = 'pending_verification';
				if ($paymentAmount >= $orderTotal) {
					// Paid everything - but still needs verification
					$order->remaining_balance = 0;
				} else {
					// Partial payment (at this point, validation ensures $paymentAmount >= $orderRequired)
					$order->remaining_balance = $remainingBalance;
				}
				$order->payment_method = 'GCash';
				$order->save();
				// If this is a child order, update siblings/parent appropriately
				if ($order->parent_order_id) {
					$parent = Order::find($order->parent_order_id);
					if ($parent) {
						$parentRemaining = $parent->childOrders()->sum('remaining_balance');
						$parent->remaining_balance = $parentRemaining;
						$parent->payment_status = 'pending_verification';
						$parent->payment_method = 'GCash';
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
		], [
			'amount.required' => 'Please enter the payment amount.',
			'amount.numeric' => 'Payment amount must be a valid number.',
			'amount.min' => 'Payment amount must be greater than 0.',
			'proof.required' => 'Please upload payment proof.',
			'proof.image' => 'Payment proof must be an image file.',
			'proof.mimes' => 'Payment proof must be a JPEG, PNG, or JPG file.',
			'proof.max' => 'Payment proof file size must not exceed 5MB.',
		]);

		$order = Order::findOrFail($validated['order_id']);

		// Ensure user owns this order
		if ($order->user_id !== $user->id) {
			return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
		}

		// Check if order is already fully paid
		if ($order->isFullyPaid()) {
			return response()->json([
				'success' => false,
				'message' => 'This order is already fully paid. No additional payment is required.'
			], 422);
		}

		// Validate payment amount against required payment
		$paymentAmount = (float) $validated['amount'];
		$requiredAmount = (float) ($order->required_payment_amount ?? $order->total_amount);
		
		// Allow small tolerance for rounding (0.01)
		$tolerance = 0.01;
		if (abs($paymentAmount - $requiredAmount) > $tolerance) {
			return response()->json([
				'success' => false,
				'message' => "Please enter the correct payment amount. Required: ₱" . number_format($requiredAmount, 2) . ", Entered: ₱" . number_format($paymentAmount, 2)
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
							'verification_status' => 'pending',
							'proof_image' => $path,
						]);
						// Update child order payment status to pending_verification
						$childTotal = (float) $child->total_amount;
						$child->payment_method = 'Bank Transfer';
						$child->payment_status = 'pending_verification';
						if ($alloc >= $childTotal) {
							$child->remaining_balance = 0;
						} else {
							$child->remaining_balance = max(0, $childRequired - $alloc);
						}
						$child->save();
						$remaining -= $alloc;
					}
					// Update parent aggregate - keep as pending_verification until all children are verified
					$parentRemaining = $order->childOrders()->sum('remaining_balance');
					$order->payment_method = 'Bank Transfer';
					$order->payment_status = 'pending_verification';
					$order->remaining_balance = $parentRemaining;
					$order->save();
					return ['payments' => $createdPayments, 'path' => $path];
				}
				// Single order path
				$payment = Payment::create([
					'order_id' => $order->id,
					'method' => 'bank',
					'amount' => $paymentAmount,
					'status' => 'pending_verification',
					'verification_status' => 'pending',
					'proof_image' => $path,
				]);
				
				// Update order payment status to pending_verification until admin verifies
				$orderTotal = (float) $order->total_amount;
				$orderRequired = (float) ($order->required_payment_amount ?? $orderTotal);
				$remainingBalance = max(0, $orderTotal - $orderRequired);
				
				$order->payment_method = 'Bank Transfer';
				$order->payment_status = 'pending_verification';
				if ($paymentAmount >= $orderTotal) {
					$order->remaining_balance = 0;
				} else {
					$order->remaining_balance = $remainingBalance;
				}
				$order->save();
				
				// If this is a child order, update parent appropriately
				if ($order->parent_order_id) {
					$parent = Order::find($order->parent_order_id);
					if ($parent) {
						$parentRemaining = $parent->childOrders()->sum('remaining_balance');
						$parent->remaining_balance = $parentRemaining;
						$parent->payment_status = 'pending_verification';
						$parent->payment_method = 'Bank Transfer';
						$parent->save();
					}
				}
				
				return ['payments' => [$payment], 'path' => $path];
			});

			return response()->json(['success' => true, 'result' => $results, 'proof_url' => Storage::url($results['path'])]);
		} catch (\Exception $e) {
			return response()->json(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()], 500);
		}
	}
}


