<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

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

        return view('employee.items-db', compact('visibleItems', 'hiddenItems', 'search'));
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
                // Store directly on the 'public' disk so files land in storage/app/public/items
                $path = $file->store('items', 'public'); // returns e.g. 'items/filename.jpg'
                $relativePath = str_replace('public/', '', $path); // safety for legacy behavior
                $item->photos()->create(['path' => $relativePath]);
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
                // Handle both legacy 'public/...'(old) and 'items/...' (new) paths
                $deletePath = preg_replace('#^public/#', '', $photo->path);
                $deletePath = ltrim($deletePath ?? '', '/');
                if (!empty($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
                $photo->delete();
            }
        }

        // Add new photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                // Store directly on the 'public' disk so files land in storage/app/public/items
                $path = $file->store('items', 'public'); // returns e.g. 'items/filename.jpg'
                $relativePath = str_replace('public/', '', $path); // safety for legacy behavior
                $item->photos()->create(['path' => $relativePath]);
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

        $deletePath = preg_replace('#^public/#', '', (string) $photo->path);
        $deletePath = ltrim($deletePath, '/');

        $deletedFromDisk = true;
        if ($deletePath !== '') {
            $deletedFromDisk = Storage::disk('public')->delete($deletePath);
        }

        $photo->delete();

        return response()->json([
            'success' => true,
            'deleted_from_disk' => $deletedFromDisk,
            'message' => 'Photo deleted successfully',
        ]);
    }
}


