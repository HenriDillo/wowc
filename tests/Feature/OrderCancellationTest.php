<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\CustomOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_standard_order_can_be_cancelled_when_pending()
    {
        $order = Order::factory()->create([
            'order_type' => 'standard',
            'status' => 'pending',
        ]);

        $this->assertTrue($order->canBeCancelled());
    }

    public function test_standard_order_cannot_be_cancelled_when_shipped()
    {
        $order = Order::factory()->create([
            'order_type' => 'standard',
            'status' => 'shipped',
        ]);

        $this->assertFalse($order->canBeCancelled());
    }

    public function test_backorder_can_be_cancelled_when_procurement_not_started()
    {
        $order = Order::factory()->create([
            'order_type' => 'backorder',
            'status' => 'pending',
        ]);

        $item = Item::factory()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'item_id' => $item->id,
            'is_backorder' => true,
            'backorder_status' => OrderItem::BO_PENDING,
        ]);

        $this->assertTrue($order->canBeCancelled());
    }

    public function test_backorder_cannot_be_cancelled_when_procurement_started()
    {
        $order = Order::factory()->create([
            'order_type' => 'backorder',
            'status' => 'processing',
        ]);

        $item = Item::factory()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'item_id' => $item->id,
            'is_backorder' => true,
            'backorder_status' => OrderItem::BO_IN_PROGRESS,
        ]);

        $this->assertFalse($order->canBeCancelled());
    }

    public function test_custom_order_can_be_cancelled_when_production_not_started()
    {
        $order = Order::factory()->create([
            'order_type' => 'custom',
            'status' => 'pending',
        ]);

        CustomOrder::factory()->create([
            'order_id' => $order->id,
            'status' => CustomOrder::STATUS_PENDING_REVIEW,
        ]);

        $this->assertTrue($order->canBeCancelled());
    }

    public function test_custom_order_cannot_be_cancelled_when_production_started()
    {
        $order = Order::factory()->create([
            'order_type' => 'custom',
            'status' => 'in_production',
        ]);

        CustomOrder::factory()->create([
            'order_id' => $order->id,
            'status' => CustomOrder::STATUS_IN_PRODUCTION,
        ]);

        $this->assertFalse($order->canBeCancelled());
    }

    public function test_completed_order_cannot_be_cancelled()
    {
        $order = Order::factory()->create([
            'order_type' => 'standard',
            'status' => 'completed',
        ]);

        $this->assertFalse($order->canBeCancelled());
    }

    public function test_already_cancelled_order_cannot_be_cancelled()
    {
        $order = Order::factory()->create([
            'order_type' => 'standard',
            'status' => 'cancelled',
        ]);

        $this->assertFalse($order->canBeCancelled());
    }
}
