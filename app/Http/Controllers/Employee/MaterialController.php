<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    // Display visible and hidden materials
    public function index()
    {
        $materials = Material::where('is_hidden', false)
            ->orderBy('name')
            ->paginate(15);

        $hiddenMaterials = Material::where('is_hidden', true)
            ->orderBy('name')
            ->paginate(15);

        return view('employee.raw-materials-db', compact('materials', 'hiddenMaterials'));
    }

    // Store new material
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:materials,name',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
        ]);

        $material = Material::create($data);

        // Optional: automatically update status
        $material->updateStatus();

        return back()->with('status', 'Material created');
    }

    // Update existing material
    public function update(Request $request, Material $material)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:materials,name,' . $material->id,
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
        ]);

        $material->update($data);

        // Optional: automatically update status
        $material->updateStatus();

        return back()->with('status', 'Material updated');
    }

    // Hide material
    public function hide(Material $material)
    {
        $material->is_hidden = true;
        $material->save();

        return back()->with('status', 'Material hidden');
    }

    // Unhide material
    public function unhide(Material $material)
    {
        $material->is_hidden = false;
        $material->save();

        return back()->with('status', 'Material unhidden');
    }
}
