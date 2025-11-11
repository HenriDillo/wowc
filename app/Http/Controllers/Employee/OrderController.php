<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
		$query = Order::query()
			->select('orders.*') // ensure unique orders across filters that may translate to joins/exists
			->distinct()
			->with(['user', 'items.item'])
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

        $orders = $query->paginate(15)->withQueryString();

        return view('employee.orders', [
            'orders' => $orders,
            'activeType' => $type,
        ]);
    }

    public function show($id)
    {
        $order = Order::with([
            'user.address',
            'items.item.photos',
            'customOrders',
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
            'carrier' => 'nullable|in:lalamove,jnt,ninjavan,2go,pickup',
            'delivered_at' => 'nullable|date',
        ]);
        
        $order = Order::findOrFail($id);
        $order->status = $validated['status'];
        
        // Save shipping fields if provided
        if (isset($validated['tracking_number'])) {
            $order->tracking_number = $validated['tracking_number'];
        }
        if (isset($validated['carrier'])) {
            $order->carrier = $validated['carrier'];
        }
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
}


