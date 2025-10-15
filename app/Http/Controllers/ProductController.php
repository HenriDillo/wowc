<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query()->where('visible', true);

        if ($request->filled('category')) {
            $query->where('category', $request->string('category')->toString());
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhere('category', 'like', "%$search%");
            });
        }

        $items = $query->orderByDesc('id')->paginate(12);

        // Build categories list from existing items (until separate categories table exists)
        $categories = Item::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->where('visible', true)
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->get()
            ->map(function ($row) {
                return (object) ['id' => $row->category, 'name' => $row->category];
            });

        return view('products', [
            'items' => $items,
            'categories' => $categories,
        ]);
    }

    public function show(Item $item)
    {
        $item->load('photos');
        return view('product-details', [
            'item' => $item,
        ]);
    }
}


