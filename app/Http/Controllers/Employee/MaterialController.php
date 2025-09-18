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
}
