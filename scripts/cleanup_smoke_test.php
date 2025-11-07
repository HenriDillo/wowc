<?php
// scripts/cleanup_smoke_test.php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

echo "Starting cleanup of smoke test data...\n";

DB::beginTransaction();
try {
    // Remove orders and order items for the smoke test user
    $user = User::where('email', 'smoke+test@example.com')->first();
    if ($user) {
        echo "Found user id={$user->id}, deleting related orders and carts...\n";

        $orders = Order::where('user_id', $user->id)->get();
        foreach ($orders as $order) {
            echo " - Deleting Order id={$order->id} and its items\n";
            OrderItem::where('order_id', $order->id)->delete();
            $order->delete();
        }

        // Carts and cart items
        $carts = Cart::where('user_id', $user->id)->get();
        foreach ($carts as $cart) {
            echo " - Deleting Cart id={$cart->id} and its items\n";
            CartItem::where('cart_id', $cart->id)->delete();
            $cart->delete();
        }

        // Finally delete the user
        echo " - Deleting user id={$user->id}\n";
        $user->delete();
    } else {
        echo "No smoke test user found.\n";
    }

    // Remove items named 'Smoke Test Item' and any related order items (defensive)
    $items = Item::where('name', 'Smoke Test Item')->get();
    foreach ($items as $item) {
        echo "Found Smoke Test Item id={$item->id}, deleting related order_items and cart_items, then item\n";
        OrderItem::where('item_id', $item->id)->delete();
        CartItem::where('item_id', $item->id)->delete();
        $item->delete();
    }

    DB::commit();
    echo "Cleanup completed successfully.\n";
    exit(0);
} catch (Throwable $e) {
    DB::rollBack();
    echo "Error during cleanup: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
