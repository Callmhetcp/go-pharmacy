<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class AdminOrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected Category $category;

    protected Product $product;

    protected Inventory $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->customer = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->category = Category::create([
            'name' => 'Cancellation Test Category',
            'slug' => 'cancellation-test-' . Str::random(8),
            'description' => 'Category used for order cancellation tests.',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Cancellation Test Paracetamol',
            'slug' => 'cancellation-paracetamol-' . Str::random(8),
            'sku' => 'CANCEL-PARA-' . Str::upper(Str::random(6)),
            'price' => 500,
            'cost_price' => 250,
            'dosage_form' => 'Tablet',
            'strength' => '500mg',
            'base_unit' => 'piece',
            'selling_unit' => 'piece',
            'units_per_selling_unit' => 1,
            'allow_partial_sale' => false,
            'requires_prescription' => false,
            'is_active' => true,
            'is_featured' => false,
            'minimum_stock' => 1,
        ]);

        $this->inventory = Inventory::create([
            'product_id' => $this->product->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
            'minimum_stock' => 1,
        ]);
    }

    protected function createOrder(
        int $quantity = 2,
        string $status = 'pending'
    ): Order {
        $order = Order::create([
            'order_number' => 'GP-CANCEL-' . Str::upper(Str::random(8)),
            'user_id' => $this->customer->id,
            'customer_name' => 'Cancellation Test Customer',
            'customer_email' => 'cancel@test.com',
            'customer_phone' => '08000000000',
            'delivery_address' => 'Test Address',
            'delivery_city' => 'Port Harcourt',
            'delivery_state' => 'Rivers',
            'subtotal' => $quantity * 500,
            'delivery_fee' => 0,
            'discount' => 0,
            'total' => $quantity * 500,
            'status' => $status,
            'payment_status' => 'unpaid',
        ]);

        $order->items()->create([
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'unit_price' => 500,
            'quantity' => $quantity,
            'selling_unit' => 'piece',
            'base_unit' => 'piece',
            'base_quantity' => $quantity,
            'subtotal' => $quantity * 500,
        ]);

        return $order->fresh('items');
    }

    public function test_admin_can_cancel_pending_order_and_release_reservation(): void
    {
        $order = $this->createOrder(2);

        app(InventoryService::class)->reserveOrder($order);

        $this->inventory->refresh();

        $this->assertSame(10, $this->inventory->quantity);
        $this->assertSame(2, $this->inventory->reserved_quantity);
        $this->assertSame(8, $this->inventory->available_quantity);

        $response = $this
            ->actingAs($this->admin)
            ->post(
                route('admin.orders.cancel', $order)
            );

        $response
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas(
                'success',
                'Order cancelled successfully and inventory reservation released.'
            );

        $order->refresh();
        $this->inventory->refresh();

        $this->assertSame(
            'cancelled',
            $order->status
        );

        $this->assertSame(
            10,
            $this->inventory->quantity
        );

        $this->assertSame(
            0,
            $this->inventory->reserved_quantity
        );

        $this->assertSame(
            10,
            $this->inventory->available_quantity
        );
    }

    public function test_cancelling_order_does_not_create_inventory_transaction(): void
    {
        $order = $this->createOrder(2);

        app(InventoryService::class)->reserveOrder($order);

        $this->assertDatabaseMissing('inventory_transactions', [
            'reference' => $order->order_number,
        ]);

        $this
            ->actingAs($this->admin)
            ->post(
                route('admin.orders.cancel', $order)
            )
            ->assertRedirect();

        $this->assertDatabaseMissing('inventory_transactions', [
            'reference' => $order->order_number,
        ]);
    }

    public function test_non_admin_cannot_cancel_order(): void
    {
        $order = $this->createOrder(2);

        app(InventoryService::class)->reserveOrder($order);

        $response = $this
            ->actingAs($this->customer)
            ->post(
                route('admin.orders.cancel', $order)
            );

        $response->assertForbidden();

        $order->refresh();
        $this->inventory->refresh();

        $this->assertSame(
            'pending',
            $order->status
        );

        $this->assertSame(
            10,
            $this->inventory->quantity
        );

        $this->assertSame(
            2,
            $this->inventory->reserved_quantity
        );
    }

    public function test_unauthenticated_user_cannot_cancel_order(): void
    {
        $order = $this->createOrder(2);

        app(InventoryService::class)->reserveOrder($order);

        $response = $this->post(
            route('admin.orders.cancel', $order)
        );

        $response->assertRedirect();

        $order->refresh();
        $this->inventory->refresh();

        $this->assertSame(
            'pending',
            $order->status
        );

        $this->assertSame(
            2,
            $this->inventory->reserved_quantity
        );
    }

    public function test_cancelled_order_cannot_be_cancelled_again(): void
    {
        $order = $this->createOrder(2);

        app(InventoryService::class)->reserveOrder($order);

        $this
            ->actingAs($this->admin)
            ->post(
                route('admin.orders.cancel', $order)
            )
            ->assertRedirect();

        $response = $this
            ->actingAs($this->admin)
            ->post(
                route('admin.orders.cancel', $order)
            );

        $response
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                'This order has already been cancelled.'
            );

        $order->refresh();
        $this->inventory->refresh();

        $this->assertSame(
            'cancelled',
            $order->status
        );

        $this->assertSame(
            10,
            $this->inventory->quantity
        );

        $this->assertSame(
            0,
            $this->inventory->reserved_quantity
        );
    }

    public function test_fulfilled_order_cannot_be_cancelled(): void
    {
        $order = $this->createOrder(2);

        $order->update([
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);

        $this
            ->actingAs($this->admin)
            ->post(
                route('admin.orders.cancel', $order)
            )
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                'This order can no longer be cancelled because fulfillment has already started.'
            );

        $order->refresh();

        $this->assertSame(
            'processing',
            $order->status
        );
    }

    public function test_cancellation_releases_only_that_orders_reservation(): void
    {
        $firstOrder = $this->createOrder(2);
        $secondOrder = $this->createOrder(3);

        $service = app(InventoryService::class);

        $service->reserveOrder($firstOrder);
        $service->reserveOrder($secondOrder);

        $this->inventory->refresh();

        $this->assertSame(
            5,
            $this->inventory->reserved_quantity
        );

        $this
            ->actingAs($this->admin)
            ->post(
                route('admin.orders.cancel', $firstOrder)
            )
            ->assertRedirect();

        $this->inventory->refresh();

        $this->assertSame(
            3,
            $this->inventory->reserved_quantity
        );

        $this->assertSame(
            7,
            $this->inventory->available_quantity
        );

        $firstOrder->refresh();
        $secondOrder->refresh();

        $this->assertSame(
            'cancelled',
            $firstOrder->status
        );

        $this->assertSame(
            'pending',
            $secondOrder->status
        );
    }
}