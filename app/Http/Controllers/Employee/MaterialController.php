<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::orderBy('name')->paginate(15);
        return view('employee.raw-materials-db', compact('materials'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:materials,name',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);
        Material::create($data);
        return back()->with('status', 'Material created');
    }

    public function update(Request $request, Material $material)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:materials,name,' . $material->id,
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);
        $material->update($data);
        return back()->with('status', 'Material updated');
    }

    public function toggleVisibility(Material $material)
    {
        $material->visible = ! $material->visible;
        $material->save();
        return back()->with('status', 'Material visibility updated');
    }
}


