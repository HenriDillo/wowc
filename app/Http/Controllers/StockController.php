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

        $item->increment('stock', $data['quantity']);

        return back()->with('status', 'Item stock updated');
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


