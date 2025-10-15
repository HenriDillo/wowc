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
        $query = Order::query()->with(['user', 'items.item'])->latest();

        // Filter by order type
        $type = $request->string('type')->toString();
        if (in_array($type, ['standard', 'preorder', 'backorder', 'custom', 'completed', 'cancelled'], true)) {
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

        // Search by order id, customer name or email
        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($sub) use ($q) {
                $sub->where('id', $q)
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
        $order = Order::with(['user.address', 'items.item.photos'])->findOrFail($id);
        // If the request expects JSON (modal usage), return JSON; otherwise render a full details page
        if (request()->expectsJson()) {
            return response()->json($order);
        }
        return view('employee.order-show', compact('order'));
    }

    public function update($id, Request $request)
    {
        $validated = $request->validate([
            // Restrict to DB enum: pending, processing, completed, cancelled, backorder, preorder
            'status' => 'required|in:pending,processing,completed,cancelled,backorder,preorder',
        ]);
        $order = Order::findOrFail($id);
        $order->status = $validated['status'];
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


