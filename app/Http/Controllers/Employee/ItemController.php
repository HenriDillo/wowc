<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::orderBy('name')->paginate(15);
        return view('employee.items-db', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);
        Item::create($data);
        return back()->with('status', 'Item created');
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);
        $item->update($data);
        return back()->with('status', 'Item updated');
    }

    public function toggleVisibility(Item $item)
    {
        $item->visible = ! $item->visible;
        $item->save();
        return back()->with('status', 'Item visibility updated');
    }
}


