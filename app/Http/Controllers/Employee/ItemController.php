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
            'deleted_from_disk' => $deletedFromDisk,
            'message' => 'Photo deleted successfully',
        ]);
    }
}


