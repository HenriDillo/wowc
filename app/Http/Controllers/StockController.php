<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Material;

class StockController extends Controller
{
    /**
     * Display the Stock Management page.
     */
    public function index()
    {
        if (!Auth::user() || !in_array(Auth::user()->role, ['admin','employee'])) {
            abort(403, 'Unauthorized');
        }

        $items = Item::orderBy('name')->with('photos')->get();
        $materials = Material::orderBy('name')->get();

        return view('employee.stock', compact('items', 'materials'));
    }

    /**
     * Add stock for an Item.
     */
    public function addItemStock(Request $request, Item $item)
    {
        if (!Auth::user() || !in_array(Auth::user()->role, ['admin','employee'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500',
        ]);

        $added = (int) $data['quantity'];

        // Use transaction to allocate stock to pending backorders
        \DB::transaction(function () use ($item, $added) {
            $item->increment('stock', $added);

            // Re-load to get updated stock
            $item->refresh();
            $available = (int) $item->stock;

            if ($available <= 0) {
                return;
            }

            // Find pending backorder order items (FIFO)
            $pending = \App\Models\OrderItem::where('item_id', $item->id)
                ->where('is_backorder', true)
                ->where('backorder_status', \App\Models\OrderItem::BO_PENDING)
                ->orderBy('created_at')
                ->get();

            foreach ($pending as $oi) {
                if ($available <= 0) break;

                if ($oi->quantity <= $available) {
                    // We can fulfill this order item now
                    $oi->backorder_status = \App\Models\OrderItem::BO_IN_PROGRESS;
                    $oi->save();

                    // Notify customer that their backorder is now ready for fulfillment
                    try {
                        $oi->loadMissing('order.user');
                        if ($oi->order && $oi->order->user && $oi->order->user->email) {
                            \Mail::to($oi->order->user->email)->send(new \App\Mail\BackorderReady($oi));
                        }
                    } catch (\Throwable $e) {
                        // swallow mail errors but log them
                        \Log::error('Failed to send backorder ready email', ['error' => $e->getMessage(), 'order_item_id' => $oi->id]);
                    }

                    $available -= $oi->quantity;
                }
            }

            // Persist remaining stock
            $item->stock = $available;
            $item->save();
        });

        return back()->with('status', 'Item stock updated and backorders allocated');
    }

    /**
     * Add stock for a Material.
     */
    public function addMaterialStock(Request $request, Material $material)
    {
        if (!Auth::user() || !in_array(Auth::user()->role, ['admin','employee'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500',
        ]);

        $material->increment('stock', $data['quantity']);

        return back()->with('status', 'Material stock increased');
    }

    /**
     * Reduce stock for a Material.
     */
    public function reduceMaterialStock(Request $request, Material $material)
    {
        if (!Auth::user() || !in_array(Auth::user()->role, ['admin','employee'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500',
        ]);

        $newStock = max(0, (int) $material->stock - (int) $data['quantity']);
        $material->update(['stock' => $newStock]);

        return back()->with('status', 'Material stock reduced');
    }
}


