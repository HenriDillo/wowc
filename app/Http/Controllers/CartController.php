<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    protected function getActiveCart(): Cart
    {
        $query = Cart::query()->where('status', 'active');
        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', Session::getId());
        }
        $cart = $query->first();
        if (!$cart) {
            $cart = Cart::create([
                'user_id' => Auth::id(),
                'session_id' => Auth::check() ? null : Session::getId(),
                'status' => 'active',
            ]);
        }
        return $cart;
    }

    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $item = Item::findOrFail($validated['item_id']);
        // For pre-order items, allow adding up to preorder limit; for back-order allow regardless of stock
        if ($item->isPreorder()) {
            $limit = $item->preorderLimit();
            $existing = CartItem::where('cart_id', optional($this->getActiveCart())->id)->where('item_id', $item->id)->first();
            $currentQty = $existing ? (int) $existing->quantity : 0;
            if ($currentQty + (int) $validated['quantity'] > $limit) {
                return response()->json(['error' => 'Pre-order limit is '.$limit.' for '.$item->name], 422);
            }
        } elseif ($item->isBackorder()) {
            // No stock enforcement on add for backorders
        } else {
            if ((int) $item->stock < (int) $validated['quantity']) {
                return response()->json(['error' => 'Insufficient stock for '.$item->name], 422);
            }
        }
        $cart = $this->getActiveCart();

        $existing = CartItem::where('cart_id', $cart->id)->where('item_id', $item->id)->first();
        if ($existing) {
            $newQty = $existing->quantity + (int) $validated['quantity'];
            if ($item->isPreorder()) {
                $limit = $item->preorderLimit();
                if ($newQty > $limit) {
                    return response()->json(['error' => 'Pre-order limit is '.$limit.' for '.$item->name], 422);
                }
            } elseif ($item->isBackorder()) {
                // No stock cap for backorders
            } else {
                if ($newQty > (int) $item->stock) {
                    return response()->json(['error' => 'Exceeds available stock for '.$item->name], 422);
                }
            }
            $existing->quantity = $newQty;
            $existing->subtotal = $existing->quantity * (float) $existing->price;
            $existing->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'item_id' => $item->id,
                'quantity' => (int) $validated['quantity'],
                'price' => (float) $item->price,
                'subtotal' => (float) $item->price * (int) $validated['quantity'],
            ]);
        }

        return $this->jsonCart();
    }

    public function removeFromCart($itemId)
    {
        $cart = $this->getActiveCart();
        CartItem::where('cart_id', $cart->id)->where('item_id', $itemId)->delete();
        return $this->jsonCart();
    }

    public function updateQuantity($itemId, Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        $cart = $this->getActiveCart();
        $ci = CartItem::where('cart_id', $cart->id)->where('item_id', $itemId)->first();
        if (!$ci) {
            return response()->json(['error' => 'Item not in cart'], 404);
        }
        $item = Item::find($itemId);
        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }
        if ($item->isPreorder()) {
            $limit = $item->preorderLimit();
            if ((int) $validated['quantity'] > $limit) {
                return response()->json(['error' => 'Pre-order limit is '.$limit.' for '.$item->name], 422);
            }
        } elseif ($item->isBackorder()) {
            // No stock cap for backorders
        } else {
            if ((int) $validated['quantity'] > (int) $item->stock) {
                return response()->json(['error' => 'Exceeds available stock for '.$item->name], 422);
            }
        }
        $ci->quantity = (int) $validated['quantity'];
        $ci->subtotal = $ci->quantity * (float) $ci->price;
        $ci->save();
        return $this->jsonCart();
    }

    public function showCart()
    {
        return $this->jsonCart();
    }

    protected function jsonCart()
    {
        $cart = $this->getActiveCart();
        $items = CartItem::with('item.photos')->where('cart_id', $cart->id)->get();
        $subtotal = (float) $items->sum('subtotal');
        return response()->json([
            'items' => $items->map(function (CartItem $ci) {
                return [
                    'item_id' => $ci->item_id,
                    'name' => $ci->item->name,
                    'price' => (float) $ci->price,
                    'quantity' => (int) $ci->quantity,
                    'subtotal' => (float) $ci->subtotal,
                    'photo' => optional($ci->item->photos->first())->url,
                    'is_preorder' => (bool) $ci->item->isPreorder(),
                    'is_backorder' => (bool) $ci->item->isBackorder(),
                    'release_date' => optional($ci->item->release_date)?->format('M d, Y'),
                    'restock_date' => optional($ci->item->restock_date)?->format('M d, Y'),
                ];
            })->values(),
            'subtotal' => round($subtotal, 2),
            'tax' => 0.0,
            'total' => round($subtotal, 2),
        ]);
    }

    public function page()
    {
        return view('cart');
    }
}


