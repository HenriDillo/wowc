<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;

class BackorderSplitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function adding_more_than_stock_creates_regular_and_backorder_cart_lines_and_checkout_splits_order()
    {
        // Create user
        $user = User::factory()->create();

        // Create item with stock 2
        $item = Item::create([
            'name' => 'Test Backorder Item',
            'price' => 100.00,
            'stock' => 2,
            'status' => 'in_stock',
            'visible' => true,
        ]);

        // Act as user
        $this->actingAs($user);

        // Add qty 5 to cart via session-enabled endpoint (use the registered route)
        $resp = $this->postJson('/cart/add', [
            'item_id' => $item->id,
            'quantity' => 5,
        ]);

        $resp->assertStatus(200)->assertJsonFragment(['subtotal' => 500.0]);

        // Inspect cart items in DB
        $cart = Cart::where('user_id', $user->id)->where('status', 'active')->first();
        $this->assertNotNull($cart);

        $cartItems = CartItem::where('cart_id', $cart->id)->where('item_id', $item->id)->get();
        $this->assertCount(2, $cartItems, 'Expected two cart lines (regular + backorder)');

        $regular = $cartItems->firstWhere('is_backorder', false);
        $back = $cartItems->firstWhere('is_backorder', true);

        $this->assertNotNull($regular);
        $this->assertNotNull($back);
        $this->assertEquals(2, $regular->quantity);
        $this->assertEquals(3, $back->quantity);

        // Now checkout
        $checkoutData = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'address_line' => '123 Test St',
            'city' => 'Test City',
            'postal_code' => '12345',
            'province' => 'Test Province',
            'phone_number' => '09171234567',
            'payment_method' => 'Bank',
        ];

    $resp = $this->post('/checkout', $checkoutData);
    $resp->assertRedirect();
    // Ensure controller didn't return errors
    $resp->assertSessionHasNoErrors();

    // Order created
    $order = Order::where('user_id', $user->id)->first();
    $this->assertNotNull($order, 'Order should be created');

        $orderItems = $order->items()->where('item_id', $item->id)->get();
        $this->assertCount(2, $orderItems, 'Expected two order items: fulfilled + backorder');

        $fulfilled = $orderItems->firstWhere('is_backorder', false);
        $bo = $orderItems->firstWhere('is_backorder', true);

        $this->assertNotNull($fulfilled);
        $this->assertNotNull($bo);
        $this->assertEquals(2, $fulfilled->quantity);
        $this->assertEquals(3, $bo->quantity);

        // Ensure stock was decremented only for fulfilled portion
        $item->refresh();
        $this->assertEquals(0, $item->stock);
    }
}
