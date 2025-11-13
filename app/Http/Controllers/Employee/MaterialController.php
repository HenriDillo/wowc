<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialStockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{
    // Display visible and hidden materials
    public function index(Request $request)
    {
        $materials = Material::where('is_hidden', false)
            ->orderBy('name')
            ->paginate(15);

        $hiddenMaterials = Material::where('is_hidden', true)
            ->orderBy('name')
            ->paginate(15);

        // Build transactions query with filters
        $query = MaterialStockTransaction::with(['material', 'user'])
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

        return view('employee.raw-materials-db', compact('materials', 'hiddenMaterials', 'recentTransactions', 'filters'));
    }

    // Store new material
    public function store(Request $request)
    {
        $data = $request->validateWithBag('createMaterial', [
            'name' => 'required|string|max:255|unique:materials,name',
            'unit' => 'required|string|max:50',
        ], [
            'name.unique' => 'Material name already exists.',
        ]);

        $material = Material::create([
            'name' => $data['name'],
            'unit' => $data['unit'],
        ]);

        return back()->with('status', 'Material created');
    }

    // Update existing material
    public function update(Request $request, Material $material)
    {
        // Default edit flow: only name and unit are editable
        $bag = 'edit_' . $material->id;
        $data = $request->validateWithBag($bag, [
            'name' => 'required|string|max:255|unique:materials,name,' . $material->id,
            'unit' => 'required|string|max:50',
        ], [
            'name.unique' => 'Material name already exists.',
        ]);

        $material->update([
            'name' => $data['name'],
            'unit' => $data['unit'],
        ]);

        return back()->with('status', 'Material updated');
    }

    // Hide material
    public function hide(Material $material)
    {
        try {
            $material->is_hidden = true;
            $material->save();
        } catch (\Throwable $e) {
            $bag = 'hide_' . $material->id;
            return back()->withErrors(['hide' => 'Failed to hide material. Please try again.'], $bag);
        }

        return back()->with('status', 'Material hidden');
    }

    // Unhide material
    public function unhide(Material $material)
    {
        $material->is_hidden = false;
        $material->save();

        return back()->with('status', 'Material unhidden');
    }

    // Add stock to material
    public function addStock(Request $request, Material $material)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500',
        ]);

        $material->increment('stock', $data['quantity']);

        // Record transaction
        MaterialStockTransaction::create([
            'material_id' => $material->id,
            'user_id' => Auth::id(),
            'type' => 'in',
            'quantity' => $data['quantity'],
            'remarks' => $data['remarks'] ?? null,
        ]);

        return back()->with('status', 'Material stock increased');
    }

    // Reduce stock from material
    public function reduceStock(Request $request, Material $material)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500',
        ]);

        $newStock = max(0, (int) $material->stock - (int) $data['quantity']);
        $material->update(['stock' => $newStock]);

        // Record transaction
        MaterialStockTransaction::create([
            'material_id' => $material->id,
            'user_id' => Auth::id(),
            'type' => 'out',
            'quantity' => $data['quantity'],
            'remarks' => $data['remarks'] ?? null,
        ]);

        return back()->with('status', 'Material stock reduced');
    }

    /**
     * Bulk add stock for multiple materials in one transaction
     */
    public function bulkAddStock(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500',
        ]);

        $transactionCount = 0;

        foreach ($data['items'] as $item) {
            $material = Material::find($item['material_id']);
            $quantity = (int)$item['quantity'];

            $material->increment('stock', $quantity);

            // Record transaction
            MaterialStockTransaction::create([
                'material_id' => $material->id,
                'user_id' => Auth::id(),
                'type' => 'in',
                'quantity' => $quantity,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $transactionCount++;
        }

        $message = "Added stock for {$transactionCount} material(s)";

        // Return JSON for AJAX requests, redirect for regular form submissions
        if ($request->wantsJson()) {
            return response()->json(['status' => $message], 200);
        }

        return back()->with('status', $message);
    }

    /**
     * Bulk reduce stock for multiple materials in one transaction
     */
    public function bulkReduceStock(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500',
        ]);

        $transactionCount = 0;

        foreach ($data['items'] as $item) {
            $material = Material::find($item['material_id']);
            $quantity = (int)$item['quantity'];

            $newStock = max(0, (int) $material->stock - $quantity);
            $material->update(['stock' => $newStock]);

            // Record transaction
            MaterialStockTransaction::create([
                'material_id' => $material->id,
                'user_id' => Auth::id(),
                'type' => 'out',
                'quantity' => $quantity,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $transactionCount++;
        }

        $message = "Reduced stock for {$transactionCount} material(s)";

        // Return JSON for AJAX requests, redirect for regular form submissions
        if ($request->wantsJson()) {
            return response()->json(['status' => $message], 200);
        }

        return back()->with('status', $message);
    }
}