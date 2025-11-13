<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemPhoto;
use App\Models\ItemStockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        // Search term
        $search = $request->input('search');

        // Visible items list
        $visibleItems = Item::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->where('visible', true)
            ->with('photos')
            ->orderBy('name')
            ->paginate(15, ['*'], 'visible_page')
            ->withQueryString();

        // Hidden items list
        $hiddenItems = Item::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->where('visible', false)
            ->with('photos')
            ->orderBy('name')
            ->paginate(15, ['*'], 'hidden_page')
            ->withQueryString();

        // Build transactions query with filters
        $query = ItemStockTransaction::with(['item', 'user'])
            ->orderBy('created_at', 'desc');

        // Filter by employee name
        if ($request->filled('employee_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('employee_name') . '%');
            });
        }

        // Filter by transaction type
        if ($request->filled('type') && in_array($request->input('type'), ['in', 'out'])) {
            $query->where('type', $request->input('type'));
        }

        // Filter by remarks/notes
        if ($request->filled('remarks')) {
            $query->where('remarks', 'like', '%' . $request->input('remarks') . '%');
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Get filtered transactions (limit to 100 for performance)
        $recentTransactions = $query->limit(100)->get();

        // Preserve filter values for form
        $filters = [
            'employee_name' => $request->input('employee_name', ''),
            'type' => $request->input('type', ''),
            'remarks' => $request->input('remarks', ''),
            'date_from' => $request->input('date_from', ''),
            'date_to' => $request->input('date_to', ''),
        ];

        return view('employee.items-db', compact('visibleItems', 'hiddenItems', 'search', 'recentTransactions', 'filters'));
    }

    public function store(Request $request)
    {
        $data = $request->validateWithBag('createItem', [
            'name' => 'required|string|max:255|unique:items,name',
            'description' => 'nullable|string|max:2000',
            'category' => 'required|string|in:Caddy,Carpet,Placemat,Others',
            'price' => 'required|numeric|min:0',
            'photos.*' => 'sometimes|file|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $item = Item::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'price' => $data['price'],
            'stock' => 0,
            'visible' => true,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                // Generate unique filename with timestamp and random string
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                // Move file to public/uploads/items directory
                $file->move(public_path('uploads/items'), $filename);
                // Store relative path in database
                $item->photos()->create(['path' => 'uploads/items/' . $filename]);
            }
        }
        return back()->with('status', 'Item created');
    }

    public function update(Request $request, Item $item)
    {
        $bag = 'edit_' . $item->id;
        $data = $request->validateWithBag($bag, [
            'name' => 'required|string|max:255|unique:items,name,' . $item->id,
            'description' => 'nullable|string|max:2000',
            'category' => 'required|string|in:Caddy,Carpet,Placemat,Others',
            'price' => 'required|numeric|min:0',
            'remove_photo_ids' => 'nullable|array',
            'remove_photo_ids.*' => 'integer',
            'photos.*' => 'sometimes|file|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $item->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'price' => $data['price'],
        ]);

        // Remove selected photos
        if (!empty($data['remove_photo_ids'])) {
            $photos = ItemPhoto::whereIn('id', $data['remove_photo_ids'])->where('item_id', $item->id)->get();
            foreach ($photos as $photo) {
                // Delete physical file if it exists
                $filePath = public_path($photo->path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $photo->delete();
            }
        }

        // Add new photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                // Generate unique filename with timestamp and random string
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                // Move file to public/uploads/items directory
                $file->move(public_path('uploads/items'), $filename);
                // Store relative path in database
                $item->photos()->create(['path' => 'uploads/items/' . $filename]);
            }
        }
        return back()->with('status', 'Item updated');
    }

    public function toggleVisibility(Item $item)
    {
        $item->visible = ! $item->visible;
        $item->save();
        return back()->with('status', 'Item visibility updated');
    }

    public function destroyPhoto(ItemPhoto $photo)
    {
        // Optional: authorize based on ownership/role
        // Gate::authorize('delete', $photo);

        $deletedFromDisk = true;
        $filePath = public_path($photo->path);
        if (file_exists($filePath)) {
            $deletedFromDisk = unlink($filePath);
        }

        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Photo deleted successfully'
        ]);
    }

    // Add stock to item
    public function addStock(Request $request, Item $item)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500',
        ]);

        $added = (int) $data['quantity'];

        // Use transaction to allocate stock to pending backorders
        \DB::transaction(function () use ($item, $added, $data) {
            $item->increment('stock', $added);

            // Record transaction
            ItemStockTransaction::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'type' => 'in',
                'quantity' => $added,
                'remarks' => $data['remarks'] ?? null,
            ]);

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

                    // Log stock transaction for backorder fulfillment
                    ItemStockTransaction::create([
                        'item_id' => $item->id,
                        'user_id' => Auth::id(),
                        'type' => 'out',
                        'quantity' => $oi->quantity,
                        'remarks' => "Backorder fulfillment - Order #{$oi->order_id}, OrderItem #{$oi->id}",
                    ]);

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

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Item stock updated and backorders allocated']);
        }
        return back()->with('status', 'Item stock updated and backorders allocated');
    }

    // Reduce stock from item
    public function reduceStock(Request $request, Item $item)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500',
        ]);

        $newStock = max(0, (int) $item->stock - (int) $data['quantity']);
        $item->update(['stock' => $newStock]);

        // Record transaction
        ItemStockTransaction::create([
            'item_id' => $item->id,
            'user_id' => Auth::id(),
            'type' => 'out',
            'quantity' => $data['quantity'],
            'remarks' => $data['remarks'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Item stock reduced']);
        }
        return back()->with('status', 'Item stock reduced');
    }

    /**
     * Bulk add stock for multiple items in one transaction
     */
    public function bulkAddStock(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500',
        ]);

        $transactionCount = 0;

        foreach ($data['items'] as $itemData) {
            $item = Item::find($itemData['item_id']);
            $quantity = (int)$itemData['quantity'];

            $item->increment('stock', $quantity);

            // Record transaction
            ItemStockTransaction::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'type' => 'in',
                'quantity' => $quantity,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $transactionCount++;
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Added stock for {$transactionCount} item(s)"]);
        }
        return back()->with('status', "Added stock for {$transactionCount} item(s)");
    }

    /**
     * Bulk reduce stock for multiple items in one transaction
     */
    public function bulkReduceStock(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500',
        ]);

        $transactionCount = 0;

        foreach ($data['items'] as $itemData) {
            $item = Item::find($itemData['item_id']);
            $quantity = (int)$itemData['quantity'];

            $newStock = max(0, (int) $item->stock - $quantity);
            $item->update(['stock' => $newStock]);

            // Record transaction
            ItemStockTransaction::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'type' => 'out',
                'quantity' => $quantity,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $transactionCount++;
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Reduced stock for {$transactionCount} item(s)"]);
        }
        return back()->with('status', "Reduced stock for {$transactionCount} item(s)");
    }
}


