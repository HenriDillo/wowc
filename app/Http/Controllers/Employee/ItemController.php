<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query();

        // Search and filter
        $search = $request->input('search');
        $visibility = $request->input('visibility'); // all|visible|hidden
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }
        if ($visibility === 'visible') {
            $query->where('visible', true);
        } elseif ($visibility === 'hidden') {
            $query->where('visible', false);
        }

        $items = $query->with('photos')->orderBy('name')->paginate(15)->withQueryString();
        return view('employee.items-db', compact('items', 'search', 'visibility'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'photos.*' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
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
                $path = $file->store('public/items');
                $item->photos()->create(['path' => $path]);
            }
        }
        return back()->with('status', 'Item created');
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'remove_photo_ids' => 'nullable|array',
            'remove_photo_ids.*' => 'integer',
            'photos.*' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
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
                Storage::delete($photo->path);
                $photo->delete();
            }
        }

        // Add new photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('public/items');
                $item->photos()->create(['path' => $path]);
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
}


