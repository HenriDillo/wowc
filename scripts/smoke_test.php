<?php
// scripts/smoke_test.php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Item;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

echo "Starting smoke test...\n";

// Ensure session has an id for cart lookup
session()->setId('cli-smoke-' . time());
$sessionId = session()->getId();
echo "Session id: $sessionId\n";

    // Create or reset test item
DB::beginTransaction();
try {
        // Create a test user and authenticate (orders require non-null user_id)
        User::where('email', 'smoke+test@example.com')->delete();
        $user = User::create([
            'first_name' => 'Smoke',
            'last_name' => 'Test',
            'email' => 'smoke+test@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => 'active',
        ]);
        Auth::login($user);
        echo "Created and logged in User id={$user->id}\n";

    // clean up any previous test items named Smoke Test Item
    Item::where('name', 'Smoke Test Item')->delete();

    $item = Item::create([
        'name' => 'Smoke Test Item',
        'price' => 100.00,
        'stock' => 2,
        'status' => 'in_stock',
    ]);

    echo "Created Item id={$item->id} stock={$item->stock}\n";

    // Create a cart for this user
    $cart = Cart::create([
        'user_id' => $user->id,
        'session_id' => null,
        'status' => 'active',
    ]);
    echo "Created Cart id={$cart->id}\n";

    // Add a single cart line requesting qty 5 (exceeds stock)
    $ci = CartItem::create([
        'cart_id' => $cart->id,
        'item_id' => $item->id,
        'quantity' => 5,
        'price' => (float) $item->price,
        'subtotal' => 5 * (float) $item->price,
        'is_backorder' => false,
    ]);
    echo "Created CartItem id={$ci->id} quantity={$ci->quantity}\n";

    // Prepare request data for checkout
    $requestData = [
        'first_name' => 'Smoke',
        'last_name' => 'Test',
        'address_line' => '123 Test St',
        'city' => 'Testville',
        'postal_code' => '0000',
        'province' => 'Test Province',
        'phone_number' => '0000000000',
        'payment_method' => 'Bank',
    ];

    $request = Request::create('/checkout', 'POST', $requestData);

    // Call the CheckoutController store method
    $controller = new App\Http\Controllers\CheckoutController();
    $response = $controller->store($request);

    // Inspect created order
    $order = Order::latest()->first();
    if (!$order) {
        echo "No order created.\n";
        echo "Controller response class: " . get_class($response) . "\n";
        // Dump session errors if present
        $errors = session()->get('errors');
        if ($errors) {
            echo "Session errors present:\n";
            if (is_object($errors) && method_exists($errors, 'all')) {
                print_r($errors->all());
            } else {
                print_r($errors);
            }
        }
        echo "Session dump:\n";
        print_r(session()->all());
        DB::rollBack();
        exit(1);
    }

    echo "Order created id={$order->id} status={$order->status} total={$order->total_amount}\n";
    $items = $order->items()->get();
    foreach ($items as $oi) {
        echo "OrderItem id={$oi->id} item_id={$oi->item_id} qty={$oi->quantity} is_backorder=" . ($oi->is_backorder ? 'true' : 'false') . " backorder_status={$oi->backorder_status}\n";
    }

    DB::commit();
    echo "Smoke test completed.\n";
} catch (Throwable $e) {
    DB::rollBack();
    echo "Error during smoke test: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

// Exit success
exit(0);
