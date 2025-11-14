<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomOrderController extends Controller
{
	public function show($id)
	{
		// Only authenticated employees should access; caller group already has auth middleware.
		$user = Auth::user();
		if (!$user || !$user->isEmployee()) {
			abort(403);
		}

		$customOrder = CustomOrder::with(['order.user'])->findOrFail($id);

		return view('employee.custom-order-show', compact('customOrder'));
	}

	public function update(Request $request, $id)
	{
		$user = Auth::user();
		if (!$user || !$user->isEmployee()) {
			abort(403);
		}

		$validated = $request->validate([
			'price_estimate' => 'required|numeric|min:0',
			'admin_notes' => 'nullable|string',
		]);

		$customOrder = CustomOrder::with('order')->findOrFail($id);

		$customOrder->price_estimate = $validated['price_estimate'];
		$customOrder->admin_notes = $validated['admin_notes'] ?? null;
		// Keep status as pending_review until explicit confirmation step exists
		$customOrder->save();

		return redirect()
			->back()
			->with('success', 'Custom order review updated. Status remains Pending.');
	}

	public function confirm(Request $request, $id)
	{
		$user = Auth::user();
		if (!$user || !$user->isEmployee()) {
			abort(403);
		}

		$validated = $request->validate([
			'price_estimate' => 'required|numeric|min:0',
			'admin_notes' => 'nullable|string',
			'estimated_completion_date' => 'required|date|after_or_equal:today',
		]);

		$customOrder = CustomOrder::with('order')->findOrFail($id);

		$customOrder->price_estimate = $validated['price_estimate'];
		$customOrder->admin_notes = $validated['admin_notes'] ?? null;
		$customOrder->estimated_completion_date = $validated['estimated_completion_date'];
		// Move to Approved (awaiting payment); production will begin after payment is recorded
		$customOrder->status = CustomOrder::STATUS_APPROVED;
		$customOrder->save();

		if ($customOrder->order) {
			// Awaiting customer payment
			$customOrder->order->status = Order::STATUS_PENDING;
			$customOrder->order->total_amount = $customOrder->price_estimate ?? $customOrder->order->total_amount;
			$customOrder->order->required_payment_amount = ($customOrder->price_estimate ?? $customOrder->order->total_amount) * 0.5;
			$customOrder->order->remaining_balance = ($customOrder->price_estimate ?? $customOrder->order->total_amount) * 0.5;
			$customOrder->order->payment_status = 'unpaid';
			$customOrder->order->save();
		}

		return redirect()
			->back()
			->with('success', 'Custom order confirmed and awaiting payment.');
	}

	public function accept(Request $request, $id)
	{
		$user = Auth::user();
		if (!$user || !$user->isEmployee()) {
			abort(403);
		}

		$validated = $request->validate([
			'price_estimate' => 'required|numeric|min:0',
			'estimated_completion_date' => 'required|date|after_or_equal:today',
			'admin_notes' => 'nullable|string',
		]);

		$customOrder = CustomOrder::with('order')->findOrFail($id);

		// Only allow accepting if status is pending_review
		if ($customOrder->status !== CustomOrder::STATUS_PENDING_REVIEW) {
			return redirect()
				->back()
				->with('error', 'This order can only be accepted when it is in pending review status.');
		}

		$customOrder->price_estimate = $validated['price_estimate'];
		$customOrder->estimated_completion_date = $validated['estimated_completion_date'];
		$customOrder->admin_notes = $validated['admin_notes'] ?? null;
		$customOrder->rejection_note = null; // Clear any previous rejection note
		$customOrder->status = CustomOrder::STATUS_APPROVED;
		$customOrder->save();

		if ($customOrder->order) {
			// Update order to show as accepted / pending payment
			$customOrder->order->status = Order::STATUS_PENDING;
			$customOrder->order->total_amount = $customOrder->price_estimate;
			$customOrder->order->required_payment_amount = $customOrder->price_estimate * 0.5; // 50% down payment
			$customOrder->order->remaining_balance = $customOrder->price_estimate * 0.5;
			$customOrder->order->payment_status = 'unpaid';
			$customOrder->order->save();
		}

		return redirect()
			->back()
			->with('success', 'Custom order accepted. Status updated to Accepted / Pending Payment.');
	}

	public function reject(Request $request, $id)
	{
		$user = Auth::user();
		if (!$user || !$user->isEmployee()) {
			abort(403);
		}

		$validated = $request->validate([
			'rejection_note' => 'required|string|min:10',
		], [
			'rejection_note.required' => 'Please provide a reason for rejection.',
			'rejection_note.min' => 'The rejection reason must be at least 10 characters.',
		]);

		$customOrder = CustomOrder::with('order')->findOrFail($id);

		// Only allow rejecting if status is pending_review
		if ($customOrder->status !== CustomOrder::STATUS_PENDING_REVIEW) {
			return redirect()
				->back()
				->with('error', 'This order can only be rejected when it is in pending review status.');
		}

		$customOrder->rejection_note = $validated['rejection_note'];
		$customOrder->status = CustomOrder::STATUS_REJECTED;
		$customOrder->save();

		if ($customOrder->order) {
			// Update order status to cancelled
			$customOrder->order->status = Order::STATUS_CANCELLED;
			$customOrder->order->save();
		}

		return redirect()
			->back()
			->with('success', 'Custom order rejected. The customer will be notified with the rejection reason.');
	}
}


