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

class MixedOrderSplitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test mixed order creation when customer checks out with both standard and backorder items
     * @test
     */
    public function mixed_order_checkout_creates_parent_and_sub_orders()
    {
        $user = User::factory()->create();

        // Create standard item with stock
        $standardItem = Item::create([
            'name' => 'Standard Item',
            'price' => 100.00,
            'stock' => 5,
            'status' => 'in_stock',
            'visible' => true,
        ]);

        // Create backorder item (out of stock)
        $backorderItem = Item::create([
            'name' => 'Backorder Item',
            'price' => 200.00,
            'stock' => 0,
            'status' => 'back_order',
            'visible' => true,
        ]);

        $this->actingAs($user);

        // Add standard item to cart
        $this->postJson('/cart/add', ['item_id' => $standardItem->id, 'quantity' => 2]);

        // Add backorder item to cart
        $this->postJson('/cart/add', ['item_id' => $backorderItem->id, 'quantity' => 1]);

        // Checkout with mixed items
        $checkoutData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line' => '123 Test St',
            'city' => 'Test City',
            'postal_code' => '12345',
            'province' => 'Test Province',
            'phone_number' => '09171234567',
            'payment_method' => 'Bank',
        ];

        $response = $this->post('/checkout', $checkoutData);
        $response->assertSessionHasNoErrors();

        // Verify parent order was created
        $parentOrder = Order::where('user_id', $user->id)
            ->where('order_type', 'mixed')
            ->first();

        $this->assertNotNull($parentOrder, 'Parent order should be created for mixed checkout');
        $this->assertNull($parentOrder->parent_order_id, 'Parent order should have no parent_order_id');

        // Verify child orders were created
        $standardOrder = Order::where('parent_order_id', $parentOrder->id)
            ->where('order_type', 'standard')
            ->first();

        $backorderOrder = Order::where('parent_order_id', $parentOrder->id)
            ->where('order_type', 'backorder')
            ->first();

        $this->assertNotNull($standardOrder, 'Standard sub-order should be created');
        $this->assertNotNull($backorderOrder, 'Backorder sub-order should be created');

        // Verify items were split correctly
        $stdItems = $standardOrder->items()->get();
        $boItems = $backorderOrder->items()->get();

        $this->assertCount(1, $stdItems, 'Standard order should have 1 item type');
        $this->assertCount(1, $boItems, 'Backorder order should have 1 item type');

        $stdItem = $stdItems->first();
        $boItem = $boItems->first();

        $this->assertEquals($standardItem->id, $stdItem->item_id);
        $this->assertEquals(2, $stdItem->quantity);
        $this->assertFalse($stdItem->is_backorder);

        $this->assertEquals($backorderItem->id, $boItem->item_id);
        $this->assertEquals(1, $boItem->quantity);
        $this->assertTrue($boItem->is_backorder);
    }

    /**
     * Test payment calculation for mixed orders (100% standard + 50% backorder)
     * @test
     */
    public function mixed_order_payment_calculation_correct()
    {
        $user = User::factory()->create();

        $standardItem = Item::create([
            'name' => 'Standard Item',
            'price' => 100.00,
            'stock' => 5,
            'status' => 'in_stock',
            'visible' => true,
        ]);

        $backorderItem = Item::create([
            'name' => 'Backorder Item',
            'price' => 200.00,
            'stock' => 0,
            'status' => 'back_order',
            'visible' => true,
        ]);

        $this->actingAs($user);

        // Add items
        $this->postJson('/cart/add', ['item_id' => $standardItem->id, 'quantity' => 2]);
        $this->postJson('/cart/add', ['item_id' => $backorderItem->id, 'quantity' => 1]);

        // Get checkout page to verify payment calculation
        $response = $this->get('/checkout');
        $response->assertOk();
        $response->assertViewHas('requiredPaymentAmount');

        // Standard total: 100 * 2 = 200
        // Backorder total: 200 * 1 = 200
        // Required payment: 200 + (200 * 0.5) = 300

        $requiredAmount = $response->viewData('requiredPaymentAmount');
        $this->assertEquals(300.00, $requiredAmount, 'Required payment should be 100% of standard + 50% of backorder');
    }

    /**
     * Test parent order shows correct totals
     * @test
     */
    public function parent_order_aggregates_child_order_totals()
    {
        $user = User::factory()->create();

        $standardItem = Item::create([
            'name' => 'Standard Item',
            'price' => 100.00,
            'stock' => 5,
            'status' => 'in_stock',
            'visible' => true,
        ]);

        $backorderItem = Item::create([
            'name' => 'Backorder Item',
            'price' => 200.00,
            'stock' => 0,
            'status' => 'back_order',
            'visible' => true,
        ]);

        $this->actingAs($user);

        $this->postJson('/cart/add', ['item_id' => $standardItem->id, 'quantity' => 2]);
        $this->postJson('/cart/add', ['item_id' => $backorderItem->id, 'quantity' => 1]);

        $checkoutData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line' => '123 Test St',
            'city' => 'Test City',
            'postal_code' => '12345',
            'province' => 'Test Province',
            'phone_number' => '09171234567',
            'payment_method' => 'Bank',
        ];

        $this->post('/checkout', $checkoutData);

        $parentOrder = Order::where('order_type', 'mixed')->first();

        // Parent total = 200 (standard) + 200 (backorder) = 400
        $this->assertEquals(400.00, $parentOrder->total_amount);

        // Required payment = 200 + 100 = 300
        $this->assertEquals(300.00, $parentOrder->required_payment_amount);

        // Remaining balance = 100 (50% of backorder)
        $this->assertEquals(100.00, $parentOrder->remaining_balance);
    }

    /**
     * Test child orders have correct payment amounts
     * @test
     */
    public function child_orders_have_correct_payment_requirements()
    {
        $user = User::factory()->create();

        $standardItem = Item::create([
            'name' => 'Standard Item',
            'price' => 150.00,
            'stock' => 10,
            'status' => 'in_stock',
            'visible' => true,
        ]);

        $backorderItem = Item::create([
            'name' => 'Backorder Item',
            'price' => 100.00,
            'stock' => 0,
            'status' => 'back_order',
            'visible' => true,
        ]);

        $this->actingAs($user);

        $this->postJson('/cart/add', ['item_id' => $standardItem->id, 'quantity' => 2]);
        $this->postJson('/cart/add', ['item_id' => $backorderItem->id, 'quantity' => 3]);

        $checkoutData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line' => '123 Test St',
            'city' => 'Test City',
            'postal_code' => '12345',
            'province' => 'Test Province',
            'phone_number' => '09171234567',
            'payment_method' => 'Bank',
        ];

        $this->post('/checkout', $checkoutData);

        $parentOrder = Order::where('order_type', 'mixed')->first();
        $standardOrder = $parentOrder->childOrders()->where('order_type', 'standard')->first();
        $backorderOrder = $parentOrder->childOrders()->where('order_type', 'backorder')->first();

        // Standard order: total = 150 * 2 = 300, required = 300 (100%)
        $this->assertEquals(300.00, $standardOrder->total_amount);
        $this->assertEquals(300.00, $standardOrder->required_payment_amount);
        $this->assertEquals(0.00, $standardOrder->remaining_balance);

        // Backorder order: total = 100 * 3 = 300, required = 150 (50%)
        $this->assertEquals(300.00, $backorderOrder->total_amount);
        $this->assertEquals(150.00, $backorderOrder->required_payment_amount);
        $this->assertEquals(150.00, $backorderOrder->remaining_balance);
    }

    /**
     * Test customer can view parent order with child orders listed
     * @test
     */
    public function customer_views_parent_order_with_child_orders()
    {
        $user = User::factory()->create();

        $standardItem = Item::create([
            'name' => 'Standard Item',
            'price' => 100.00,
            'stock' => 5,
            'status' => 'in_stock',
            'visible' => true,
        ]);

        $backorderItem = Item::create([
            'name' => 'Backorder Item',
            'price' => 200.00,
            'stock' => 0,
            'status' => 'back_order',
            'visible' => true,
        ]);

        $this->actingAs($user);

        $this->postJson('/cart/add', ['item_id' => $standardItem->id, 'quantity' => 2]);
        $this->postJson('/cart/add', ['item_id' => $backorderItem->id, 'quantity' => 1]);

        $checkoutData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line' => '123 Test St',
            'city' => 'Test City',
            'postal_code' => '12345',
            'province' => 'Test Province',
            'phone_number' => '09171234567',
            'payment_method' => 'Bank',
        ];

        $this->post('/checkout', $checkoutData);

        $parentOrder = Order::where('order_type', 'mixed')->first();

        // View parent order details
        $response = $this->get(route('customer.orders.show', $parentOrder->id));
        $response->assertOk();
        $response->assertViewHas('order', $parentOrder);

        // Check that child orders are loaded
        $viewOrder = $response->viewData('order');
        $this->assertCount(2, $viewOrder->childOrders);
    }

    /**
     * Test employee sees parent order with sub-orders in list
     * @test
     */
    public function employee_sees_mixed_order_with_sub_orders_in_list()
    {
        $user = User::factory()->create();
        $employee = User::factory()->create(['role' => 'employee']);

        $standardItem = Item::create([
            'name' => 'Standard Item',
            'price' => 100.00,
            'stock' => 5,
            'status' => 'in_stock',
            'visible' => true,
        ]);

        $backorderItem = Item::create([
            'name' => 'Backorder Item',
            'price' => 200.00,
            'stock' => 0,
            'status' => 'back_order',
            'visible' => true,
        ]);

        $this->actingAs($user);

        $this->postJson('/cart/add', ['item_id' => $standardItem->id, 'quantity' => 2]);
        $this->postJson('/cart/add', ['item_id' => $backorderItem->id, 'quantity' => 1]);

        $checkoutData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line' => '123 Test St',
            'city' => 'Test City',
            'postal_code' => '12345',
            'province' => 'Test Province',
            'phone_number' => '09171234567',
            'payment_method' => 'Bank',
        ];

        $this->post('/checkout', $checkoutData);

        // View as employee
        $this->actingAs($employee);
        $response = $this->get('/employee/orders');
        $response->assertOk();

        $orders = $response->viewData('orders');
        $parentOrder = $orders->first(fn($o) => $o->order_type === 'mixed');

        $this->assertNotNull($parentOrder, 'Parent mixed order should be visible in employee list');
        $this->assertCount(2, $parentOrder->childOrders, 'Child orders should be loaded');
    }

    /**
     * Test partial stock fulfillment creates both standard and backorder items in correct order
     * @test
     */
    public function partial_stock_item_splits_to_standard_and_backorder()
    {
        $user = User::factory()->create();

        // Item with only partial stock
        $item = Item::create([
            'name' => 'Partial Stock Item',
            'price' => 100.00,
            'stock' => 2,
            'status' => 'in_stock',
            'visible' => true,
        ]);

        // Backorder item
        $backorderItem = Item::create([
            'name' => 'Pure Backorder Item',
            'price' => 200.00,
            'stock' => 0,
            'status' => 'back_order',
            'visible' => true,
        ]);

        $this->actingAs($user);

        // Add 5 qty of partial stock item (only 2 in stock)
        $this->postJson('/cart/add', ['item_id' => $item->id, 'quantity' => 5]);

        // Add backorder item
        $this->postJson('/cart/add', ['item_id' => $backorderItem->id, 'quantity' => 1]);

        $checkoutData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line' => '123 Test St',
            'city' => 'Test City',
            'postal_code' => '12345',
            'province' => 'Test Province',
            'phone_number' => '09171234567',
            'payment_method' => 'Bank',
        ];

        $this->post('/checkout', $checkoutData);

        $parentOrder = Order::where('order_type', 'mixed')->first();
        $standardOrder = $parentOrder->childOrders()->where('order_type', 'standard')->first();
        $backorderOrder = $parentOrder->childOrders()->where('order_type', 'backorder')->first();

        // Standard order should have the pure backorder item only
        // (since partial stock item is handled separately)
        $stdItems = $standardOrder->items()->get();
        $this->assertGreaterThan(0, $stdItems->count(), 'Standard order should have items');

        // Backorder order should have pure backorder item and partial stock remainder
        $boItems = $backorderOrder->items()->get();
        $this->assertGreaterThan(0, $boItems->count(), 'Backorder order should have items');
        
        // Total items across both orders should account for all quantities
        $totalQty = $stdItems->sum('quantity') + $boItems->sum('quantity');
        $this->assertEquals(6, $totalQty, 'Total quantity should be 5 (partial item) + 1 (backorder item)');
    }

    /**
     * Test that backorder order payment is only 50% of total
     * @test
     */
    public function backorder_order_requires_50_percent_payment()
    {
        $user = User::factory()->create();

        // Create backorder item (out of stock)
        $backorderItem = Item::create([
            'name' => 'Backorder Item',
            'price' => 1000.00,
            'stock' => 0,
            'status' => 'back_order',
            'visible' => true,
        ]);

        $this->actingAs($user);

        // Add only backorder item to cart
        $this->postJson('/cart/add', ['item_id' => $backorderItem->id, 'quantity' => 1]);

        $checkoutData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line' => '123 Test St',
            'city' => 'Test City',
            'postal_code' => '12345',
            'province' => 'Test Province',
            'phone_number' => '09171234567',
            'payment_method' => 'GCash',
        ];

        $response = $this->post('/checkout', $checkoutData);
        $response->assertRedirect();

        // Find the backorder order
        $order = Order::where('order_type', 'backorder')->first();
        $this->assertNotNull($order);
        
        $this->assertEquals(1000.00, $order->total_amount, 'Total amount should be 1000');
        $this->assertEquals(500.00, $order->required_payment_amount, 'Required payment should be 50% = 500');
        
        // Try to pay less than required - should fail
        $paymentResponse = $this->postJson('/payments/gcash', [
            'order_id' => $order->id,
            'amount' => 400.00,
            'reference' => 'TEST123',
        ]);
        $paymentResponse->assertStatus(422);
        $paymentResponse->assertJsonFragment(['success' => false]);
        
        // Pay exactly the required 50%
        $paymentResponse = $this->postJson('/payments/gcash', [
            'order_id' => $order->id,
            'amount' => 500.00,
            'reference' => 'TEST123',
        ]);
        $paymentResponse->assertStatus(200);
        $paymentResponse->assertJsonFragment(['success' => true]);
        
        // Refresh and check payment status
        $order->refresh();
        $this->assertEquals('partially_paid', $order->payment_status, 'Payment status should be partially_paid');
        $this->assertEquals(500.00, $order->remaining_balance, 'Remaining balance should be 500 (the other 50%)');
    }

    /**
     * Test that custom order payment is only 50% of total
     * @test
     */
    public function custom_order_requires_50_percent_payment()
    {
        $user = User::factory()->create();

        // Create a custom order manually
        $order = Order::create([
            'user_id' => $user->id,
            'order_type' => 'custom',
            'status' => 'pending',
            'total_amount' => 2000.00,
            'payment_status' => 'unpaid',
        ]);

        // Set required payment amount
        $order->required_payment_amount = $order->calculateRequiredPaymentAmount();
        $order->save();

        $this->actingAs($user);
        
        $this->assertEquals(2000.00, $order->total_amount);
        $this->assertEquals(1000.00, $order->required_payment_amount, 'Required payment should be 50% = 1000');
        
        // Try to pay less than required - should fail
        $paymentResponse = $this->postJson('/payments/gcash', [
            'order_id' => $order->id,
            'amount' => 900.00,
            'reference' => 'TEST456',
        ]);
        $paymentResponse->assertStatus(422);
        
        // Pay exactly the required 50%
        $paymentResponse = $this->postJson('/payments/gcash', [
            'order_id' => $order->id,
            'amount' => 1000.00,
            'reference' => 'TEST456',
        ]);
        $paymentResponse->assertStatus(200);
        $paymentResponse->assertJsonFragment(['success' => true]);
        
        // Refresh and check payment status
        $order->refresh();
        $this->assertEquals('partially_paid', $order->payment_status);
        $this->assertEquals(1000.00, $order->remaining_balance, 'Remaining balance should be 1000 (the other 50%)');
    }

    /**
     * Test that standard order payment requires 100%
     * @test
     */
    public function standard_order_requires_100_percent_payment()
    {
        $user = User::factory()->create();

        // Create standard item with stock
        $standardItem = Item::create([
            'name' => 'Standard Item',
            'price' => 1000.00,
            'stock' => 5,
            'status' => 'in_stock',
            'visible' => true,
        ]);

        $this->actingAs($user);

        // Add only standard item to cart
        $this->postJson('/cart/add', ['item_id' => $standardItem->id, 'quantity' => 1]);

        $checkoutData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line' => '123 Test St',
            'city' => 'Test City',
            'postal_code' => '12345',
            'province' => 'Test Province',
            'phone_number' => '09171234567',
            'payment_method' => 'GCash',
        ];

        $response = $this->post('/checkout', $checkoutData);
        $response->assertRedirect();

        // Find the standard order
        $order = Order::where('order_type', 'standard')->first();
        $this->assertNotNull($order);
        
        $this->assertEquals(1000.00, $order->total_amount);
        $this->assertEquals(1000.00, $order->required_payment_amount, 'Required payment should be 100%');
        
        // Try to pay less - should fail
        $paymentResponse = $this->postJson('/payments/gcash', [
            'order_id' => $order->id,
            'amount' => 500.00,
            'reference' => 'TEST789',
        ]);
        $paymentResponse->assertStatus(422);
        
        // Pay the full amount
        $paymentResponse = $this->postJson('/payments/gcash', [
            'order_id' => $order->id,
            'amount' => 1000.00,
            'reference' => 'TEST789',
        ]);
        $paymentResponse->assertStatus(200);
        $paymentResponse->assertJsonFragment(['success' => true]);
        
        // Refresh and check payment status
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status, 'Payment status should be paid');
        $this->assertEquals(0, $order->remaining_balance, 'Remaining balance should be 0');
    }
}
