<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CustomCartItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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

	public function addCustomToCart(Request $request)
	{
		$validated = $request->validate([
			'custom_name' => 'required|string|max:255',
			'description' => 'required|string',
			'customization_details' => 'required|array',
			'customization_details.dimensions' => 'required|string',
			'reference_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
			'quantity' => 'required|integer|min:1',
		]);

		$cart = $this->getActiveCart();

		if ($request->hasFile('reference_image')) {
			$path = $request->file('reference_image')->store('custom-orders', 'public');
			$validated['reference_image_path'] = $path;
		}

		$customCartItem = CustomCartItem::create([
			'session_id' => $cart->session_id,
			'custom_name' => $validated['custom_name'],
			'description' => $validated['description'],
			'customization_details' => $validated['customization_details'],
			'reference_image_path' => $validated['reference_image_path'] ?? null,
			'quantity' => $validated['quantity'],
		]);

		if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
			$payload = $this->jsonCart()->getData(true);
			$payload['message'] = 'Custom order added to cart.';
			return response()->json($payload);
		}

		return redirect()->route('cart')->with('success', 'Custom order added to cart.');
	}

	public function removeCustomFromCart($id)
	{
		$cart = $this->getActiveCart();
		$customItem = CustomCartItem::where('id', $id)
			->where('session_id', $cart->session_id)
			->first();

		if ($customItem) {
			if ($customItem->reference_image_path) {
				Storage::disk('public')->delete($customItem->reference_image_path);
			}
			$customItem->delete();
		}

		return $this->jsonCart();
	}

	public function addToCart(Request $request)
	{
		Log::info('CartController@addToCart called', ['payload' => $request->all(), 'session' => Session::getId(), 'user' => Auth::id()]);
		$validated = $request->validate([
			'item_id' => 'required|exists:items,id',
			'quantity' => 'required|integer|min:1',
		]);

		$item = Item::findOrFail($validated['item_id']);
		$requested = (int) $validated['quantity'];
		$available = (int) max(0, $item->stock ?? 0);

		$cart = $this->getActiveCart();

		$existingReg = CartItem::where('cart_id', $cart->id)
			->where('item_id', $item->id)
			->where(function ($q) { $q->whereNull('is_backorder')->orWhere('is_backorder', false); })
			->first();
		$existingBack = CartItem::where('cart_id', $cart->id)
			->where('item_id', $item->id)
			->where('is_backorder', true)
			->first();

		$message = null;

		if ($item->isBackorder()) {
			$addBack = $requested;
			if ($existingBack) {
				$existingBack->quantity += $addBack;
				$existingBack->subtotal = $existingBack->quantity * (float) $existingBack->price;
				$existingBack->save();
				Log::info('CartController@addToCart updated existing backorder item', ['cart_id' => $cart->id, 'item_id' => $item->id, 'quantity' => $existingBack->quantity]);
			} else {
				CartItem::create([
					'cart_id' => $cart->id,
					'item_id' => $item->id,
					'quantity' => $addBack,
					'price' => (float) $item->price,
					'subtotal' => (float) $item->price * $addBack,
					'is_backorder' => true,
				]);
				Log::info('CartController@addToCart created backorder cart item', ['cart_id' => $cart->id, 'item_id' => $item->id, 'quantity' => $addBack]);
			}

			$message = 'Item added as back order.';
		} else {
			$existingRegQty = $existingReg ? (int) $existingReg->quantity : 0;
			$canAddReg = max(0, $available - $existingRegQty);
			$addReg = min($requested, $canAddReg);
			$addBack = $requested - $addReg;

			if ($addReg > 0) {
				if ($existingReg) {
					$existingReg->quantity += $addReg;
					$existingReg->subtotal = $existingReg->quantity * (float) $existingReg->price;
					$existingReg->is_backorder = false;
					$existingReg->save();
					Log::info('CartController@addToCart updated existing regular item', ['cart_id' => $cart->id, 'item_id' => $item->id, 'quantity' => $existingReg->quantity]);
				} else {
					CartItem::create([
						'cart_id' => $cart->id,
						'item_id' => $item->id,
						'quantity' => $addReg,
						'price' => (float) $item->price,
						'subtotal' => (float) $item->price * $addReg,
						'is_backorder' => false,
					]);
					Log::info('CartController@addToCart created regular cart item', ['cart_id' => $cart->id, 'item_id' => $item->id, 'quantity' => $addReg]);
				}
			}

			if ($addBack > 0) {
				if ($existingBack) {
					$existingBack->quantity += $addBack;
					$existingBack->subtotal = $existingBack->quantity * (float) $existingBack->price;
					$existingBack->save();
					Log::info('CartController@addToCart updated existing backorder item', ['cart_id' => $cart->id, 'item_id' => $item->id, 'quantity' => $existingBack->quantity]);
				} else {
					CartItem::create([
						'cart_id' => $cart->id,
						'item_id' => $item->id,
						'quantity' => $addBack,
						'price' => (float) $item->price,
						'subtotal' => (float) $item->price * $addBack,
						'is_backorder' => true,
					]);
					Log::info('CartController@addToCart created backorder cart item', ['cart_id' => $cart->id, 'item_id' => $item->id, 'quantity' => $addBack]);
				}
			}

			if ($addBack > 0) {
				$message = 'Some items exceed current stock and the excess was added as Back Order.';
			} else {
				$message = 'Item added to cart.';
			}
		}

		$count = CartItem::where('cart_id', $cart->id)->count();
		Log::info('CartController@addToCart finished', ['cart_id' => $cart->id, 'items_count' => $count]);

		if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
			$payload = $this->jsonCart()->getData(true);
			$payload['message'] = $message ?? 'Item added to cart';
			return response()->json($payload);
		}

		return redirect()->route('cart')->with('success', $message ?? 'Item added to cart');
	}

	public function removeFromCart($cartItemId)
	{
		$cart = $this->getActiveCart();
		$ci = CartItem::where('id', $cartItemId)->where('cart_id', $cart->id)->first();
		if ($ci) {
			$ci->delete();
		}
		return $this->jsonCart();
	}

	public function updateQuantity($cartItemId, Request $request)
	{
		$validated = $request->validate([
			'quantity' => 'required|integer|min:1',
		]);
		$cart = $this->getActiveCart();
		$ci = CartItem::where('id', $cartItemId)->where('cart_id', $cart->id)->first();
		if (!$ci) {
			return response()->json(['error' => 'Cart item not found'], 404);
		}
		$item = Item::find($ci->item_id);
		if (!$item) {
			return response()->json(['error' => 'Item not found'], 404);
		}

		$requested = (int) $validated['quantity'];
		$available = (int) max(0, $item->stock ?? 0);
		$message = null;

		if ($ci->is_backorder) {
			$ci->quantity = $requested;
			$ci->subtotal = $ci->quantity * (float) $ci->price;
			$ci->save();
			return $this->jsonCart();
		}

		if ($requested <= $available) {
			$ci->quantity = $requested;
			$ci->subtotal = $ci->quantity * (float) $ci->price;
			$ci->save();
			return $this->jsonCart();
		}

		$cap = $available;
		$excess = $requested - $cap;

		$ci->quantity = $cap;
		$ci->subtotal = $ci->quantity * (float) $ci->price;
		$ci->save();

		$existingBack = CartItem::where('cart_id', $cart->id)->where('item_id', $item->id)->where('is_backorder', true)->first();
		if ($existingBack) {
			$existingBack->quantity += $excess;
			$existingBack->subtotal = $existingBack->quantity * (float) $existingBack->price;
			$existingBack->save();
		} else {
			CartItem::create([
				'cart_id' => $cart->id,
				'item_id' => $item->id,
				'quantity' => $excess,
				'price' => (float) $item->price,
				'subtotal' => (float) $item->price * $excess,
				'is_backorder' => true,
			]);
		}

		$message = 'Quantity exceeds current stock; excess added as Back Order.';

		$payload = $this->jsonCart()->getData(true);
		$payload['message'] = $message;
		return response()->json($payload);
	}


	public function updateCustomQuantity($customCartItemId, Request $request)
	{
		$validated = $request->validate([
			'quantity' => 'required|integer|min:1',
		]);

		$cart = $this->getActiveCart();
		$ci = CustomCartItem::where('id', $customCartItemId)->where('session_id', $cart->session_id)->first();
		if (!$ci) {
			return response()->json(['error' => 'Custom cart item not found'], 404);
		}

		$ci->quantity = (int) $validated['quantity'];
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
		$customItems = CustomCartItem::where('session_id', $cart->session_id)->get();
		Log::info('CartController@jsonCart', ['cart_id' => $cart->id, 'items' => $items->count(), 'custom_items' => $customItems->count(), 'session' => Session::getId(), 'user' => Auth::id()]);

		$subtotal = (float) $items->sum('subtotal');

		$regularItems = $items->map(function (CartItem $ci) {
			return [
				'type' => 'regular',
				'cart_item_id' => $ci->id,
				'item_id' => $ci->item_id,
				'name' => $ci->item->name,
				'price' => (float) $ci->price,
				'quantity' => (int) $ci->quantity,
				'subtotal' => (float) $ci->subtotal,
				'photo' => optional($ci->item->photos->first())->url,
				'is_backorder' => (bool) ($ci->is_backorder ?? $ci->item->isBackorder()),
				'release_date' => optional($ci->item->release_date)?->format('M d, Y'),
				'restock_date' => optional($ci->item->restock_date)?->format('M d, Y'),
			];
		});

		$customCartItems = $customItems->map(function (CustomCartItem $ci) {
			return [
				'type' => 'custom',
				'cart_item_id' => $ci->id,
				'custom_name' => $ci->custom_name,
				'description' => $ci->description,
				'quantity' => (int) $ci->quantity,
				'customization_details' => $ci->customization_details,
				'photo' => $ci->reference_image_path ? Storage::url($ci->reference_image_path) : null,
			];
		});

		$allItems = $regularItems->concat($customCartItems);

		return response()->json([
			'items' => $allItems->values(),
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


