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
use Tests\TestCase;

class AdminOrderFulfillmentTest extends TestCase
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
            'name' => 'Fulfillment Test Category',
            'slug' => 'fulfillment-test-' . Str::random(8),
            'description' => 'Category used for order fulfillment tests.',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Fulfillment Test Paracetamol',
            'slug' => 'fulfillment-paracetamol-' . Str::random(8),
            'sku' => 'FULFILL-PARA-' . Str::upper(Str::random(6)),
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
        string $status = 'pending',
        string $paymentStatus = 'paid'
    ): Order {
        $order = Order::create([
            'order_number' => 'GP-FULFILL-' . Str::upper(Str::random(8)),
            'user_id' => $this->customer->id,
            'customer_name' => 'Fulfillment Test Customer',
            'customer_email' => 'fulfill@test.com',
            'customer_phone' => '08000000000',
            'delivery_address' => 'Test Address',
            'delivery_city' => 'Port Harcourt',
            'delivery_state' => 'Rivers',
            'subtotal' => $quantity * 500,
            'delivery_fee' => 0,
            'discount' => 0,
            'total' => $quantity * 500,
            'status' => $status,
            'payment_status' => $paymentStatus,
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

    public function test_admin_can_fulfill_paid_order_and_deduct_inventory(): void
    {
        $order = $this->createOrder(2);

        app(InventoryService::class)->reserveOrder($order);

        $this->inventory->refresh();

        $this->assertSame(10, $this->inventory->quantity);
        $this->assertSame(2, $this->inventory->reserved_quantity);

        $response = $this
            ->actingAs($this->admin)
            ->post(
                route('admin.orders.fulfill', $order)
            );

        $response
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas(
                'success',
                'Order fulfilled successfully and inventory deducted.'
            );

        $order->refresh();
        $this->inventory->refresh();

        $this->assertSame(
            'processing',
            $order->status
        );

        $this->assertSame(
            'paid',
            $order->payment_status
        );

        $this->assertSame(
            8,
            $this->inventory->quantity
        );

        $this->assertSame(
            0,
            $this->inventory->reserved_quantity
        );

        $this->assertSame(
            8,
            $this->inventory->available_quantity
        );
    }

    public function test_fulfillment_creates_online_sale_inventory_transaction(): void
    {
        $order = $this->createOrder(2);

        app(InventoryService::class)->reserveOrder($order);

        $this
            ->actingAs($this->admin)
            ->post(
                route('admin.orders.fulfill', $order)
            )
            ->assertRedirect();

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'inventory_id' => $this->inventory->id,
            'type' => 'online_sale',
            'quantity' => -2,
            'quantity_before' => 10,
            'quantity_after' => 8,
            'reference' => $order->order_number,
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_unpaid_order_cannot_be_fulfilled(): void
    {
        $order = $this->createOrder(
            quantity: 2,
            status: 'pending',
            paymentStatus: 'unpaid'
        );

        app(InventoryService::class)->reserveOrder($order);

        $response = $this
            ->actingAs($this->admin)
            ->post(
                route('admin.orders.fulfill', $order)
            );

        $response
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                'This order cannot be fulfilled because payment has not been completed.'
            );

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

        $this->assertDatabaseMissing('inventory_transactions', [
            'reference' => $order->order_number,
        ]);
    }

    public function test_cancelled_order_cannot_be_fulfilled(): void
    {
        $order = $this->createOrder(
            quantity: 2,
            status: 'cancelled',
            paymentStatus: 'paid'
        );

        $response = $this
            ->actingAs($this->admin)
            ->post(
                route('admin.orders.fulfill', $order)
            );

        $response
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                'This order has been cancelled and cannot be fulfilled.'
            );

        $this->inventory->refresh();

        $this->assertSame(
            10,
            $this->inventory->quantity
        );

        $this->assertSame(
            0,
            $this->inventory->reserved_quantity
        );

        $this->assertDatabaseMissing('inventory_transactions', [
            'reference' => $order->order_number,
        ]);
    }

    public function test_already_fulfilled_order_cannot_be_fulfilled_again(): void
    {
        $order = $this->createOrder(
            quantity: 2,
            status: 'processing',
            paymentStatus: 'paid'
        );

        $response = $this
            ->actingAs($this->admin)
            ->post(
                route('admin.orders.fulfill', $order)
            );

        $response
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                'This order has already been fulfilled and cannot be fulfilled again.'
            );

        $this->inventory->refresh();

        $this->assertSame(
            10,
            $this->inventory->quantity
        );

        $this->assertDatabaseMissing('inventory_transactions', [
            'reference' => $order->order_number,
        ]);
    }

    public function test_fulfillment_is_atomic_when_physical_stock_is_insufficient(): void
    {
        $order = $this->createOrder(2);

        app(InventoryService::class)->reserveOrder($order);

        $this->inventory->refresh();

        /*
         * Simulate physical stock becoming insufficient after
         * the reservation was created.
         */
        $this->inventory->update([
            'quantity' => 1,
        ]);

        $response = $this
            ->actingAs($this->admin)
            ->post(
                route('admin.orders.fulfill', $order)
            );

        $response
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                'Insufficient physical stock for Fulfillment Test Paracetamol.'
            );

        $order->refresh();
        $this->inventory->refresh();

        $this->assertSame(
            'pending',
            $order->status
        );

        $this->assertSame(
            1,
            $this->inventory->quantity
        );

        /*
         * The reservation must remain because fulfillment failed.
         */
        $this->assertSame(
            2,
            $this->inventory->reserved_quantity
        );

        $this->assertDatabaseMissing('inventory_transactions', [
            'reference' => $order->order_number,
        ]);
    }

    public function test_non_admin_cannot_fulfill_order(): void
    {
        $order = $this->createOrder(2);

        app(InventoryService::class)->reserveOrder($order);

        $response = $this
            ->actingAs($this->customer)
            ->post(
                route('admin.orders.fulfill', $order)
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

    public function test_unauthenticated_user_cannot_fulfill_order(): void
    {
        $order = $this->createOrder(2);

        app(InventoryService::class)->reserveOrder($order);

        $response = $this->post(
            route('admin.orders.fulfill', $order)
        );

        $response->assertRedirect();

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
}
