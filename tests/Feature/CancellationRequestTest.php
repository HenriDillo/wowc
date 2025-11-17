<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\CancellationRequest;
use App\Models\OrderItem;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancellationRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_request_cancellation_for_own_order()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'order_type' => 'standard',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($customer)->post(route('customer.orders.cancel', $order->id), [
            'reason' => 'I no longer need this item. Please cancel my order.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('cancellation_requests', [
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'status' => CancellationRequest::STATUS_REQUESTED,
            'requested_by' => 'customer',
        ]);
    }

    public function test_customer_cannot_request_cancellation_for_other_customer_order()
    {
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $customer1->id,
            'order_type' => 'standard',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($customer2)->post(route('customer.orders.cancel', $order->id), [
            'reason' => 'I want to cancel this order.',
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_cancel_shipped_order()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'order_type' => 'standard',
            'status' => 'shipped',
        ]);

        $response = $this->actingAs($customer)->post(route('customer.orders.cancel', $order->id), [
            'reason' => 'I want to cancel this order.',
        ]);

        $response->assertSessionHasErrors(['error']);
    }

    public function test_employee_can_approve_cancellation_request()
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'order_type' => 'standard',
            'status' => 'pending',
        ]);

        $cancellationRequest = CancellationRequest::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'reason' => 'I want to cancel.',
            'status' => CancellationRequest::STATUS_REQUESTED,
        ]);

        $response = $this->actingAs($employee)->post(route('employee.cancellations.approve', $cancellationRequest->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('cancellation_requests', [
            'id' => $cancellationRequest->id,
            'status' => CancellationRequest::STATUS_APPROVED,
            'handled_by' => $employee->id,
        ]);
    }

    public function test_employee_can_reject_cancellation_request()
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'order_type' => 'standard',
            'status' => 'pending',
        ]);

        $cancellationRequest = CancellationRequest::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'reason' => 'I want to cancel.',
            'status' => CancellationRequest::STATUS_REQUESTED,
        ]);

        $response = $this->actingAs($employee)->post(route('employee.cancellations.reject', $cancellationRequest->id), [
            'notes' => 'Order already in processing.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('cancellation_requests', [
            'id' => $cancellationRequest->id,
            'status' => CancellationRequest::STATUS_REJECTED,
            'handled_by' => $employee->id,
        ]);
    }

    public function test_cancellation_reason_is_required()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'order_type' => 'standard',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($customer)->post(route('customer.orders.cancel', $order->id), []);

        $response->assertSessionHasErrors(['reason']);
    }

    public function test_cancellation_reason_must_be_minimum_10_characters()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'order_type' => 'standard',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($customer)->post(route('customer.orders.cancel', $order->id), [
            'reason' => 'Short',
        ]);

        $response->assertSessionHasErrors(['reason']);
    }
}
