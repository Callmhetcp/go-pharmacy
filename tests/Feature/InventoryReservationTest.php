<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryReservationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Category $category;

    protected Product $product;

    protected Inventory $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category-' . fake()->unique()->randomNumber(6),
            'description' => 'Test category',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Test Paracetamol 500mg',
            'slug' => 'test-paracetamol-500mg-' . fake()->unique()->randomNumber(6),
            'sku' => 'TEST-PARA-' . fake()->unique()->randomNumber(6),
            'price' => 500,
            'cost_price' => 250,
            'dosage_form' => 'Tablet',
            'strength' => '500mg',
            'base_unit' => 'piece',
            'selling_unit' => 'piece',
            'units_per_selling_unit' => 1,
            'allow_partial_sale' => false,
            'packaging_description' => null,
            'requires_prescription' => false,
            'is_active' => true,
            'is_featured' => false,
            'image' => null,
            'minimum_stock' => 1,
        ]);

        $this->inventory = Inventory::create([
            'product_id' => $this->product->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
            'minimum_stock' => 1,
        ]);

        $this->actingAs($this->admin);
    }

    protected function createOrder(int $baseQuantity = 2): Order
    {
        $order = Order::create([
            'order_number' => 'TEST-RES-' . strtoupper(fake()->unique()->bothify('######')),
            'customer_name' => 'Reservation Test',
            'customer_email' => 'reservation@example.com',
            'customer_phone' => '08000000000',
            'delivery_address' => 'Test Address',
            'subtotal' => $baseQuantity * 500,
            'delivery_fee' => 0,
            'discount' => 0,
            'total' => $baseQuantity * 500,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $order->items()->create([
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'unit_price' => 500,
            'quantity' => $baseQuantity,
            'selling_unit' => 'piece',
            'base_unit' => 'piece',
            'base_quantity' => $baseQuantity,
            'subtotal' => $baseQuantity * 500,
        ]);

        return $order->fresh('items');
    }

    public function test_reserving_order_increases_reserved_quantity(): void
    {
        $order = $this->createOrder(2);

        app(InventoryService::class)->reserveOrder($order);

        $this->inventory->refresh();

        $this->assertSame(2, (int) $this->inventory->reserved_quantity);
    }

    public function test_reserving_order_does_not_reduce_physical_quantity(): void
    {
        $order = $this->createOrder(2);

        app(InventoryService::class)->reserveOrder($order);

        $this->inventory->refresh();

        $this->assertSame(10, (int) $this->inventory->quantity);
        $this->assertSame(2, (int) $this->inventory->reserved_quantity);
    }

    public function test_reserving_order_reduces_available_quantity(): void
    {
        $order = $this->createOrder(3);

        app(InventoryService::class)->reserveOrder($order);

        $this->inventory->refresh();

        $this->assertSame(7, (int) $this->inventory->available_quantity);
    }

    public function test_reservation_cannot_exceed_available_stock(): void
    {
        $order = $this->createOrder(11);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Insufficient stock for Test Paracetamol 500mg.'
        );

        app(InventoryService::class)->reserveOrder($order);

        $this->inventory->refresh();

        $this->assertSame(10, (int) $this->inventory->quantity);
        $this->assertSame(0, (int) $this->inventory->reserved_quantity);
    }

    public function test_releasing_order_reservation_restores_available_stock(): void
    {
        $order = $this->createOrder(4);

        $service = app(InventoryService::class);

        $service->reserveOrder($order);

        $this->inventory->refresh();

        $this->assertSame(4, (int) $this->inventory->reserved_quantity);
        $this->assertSame(6, (int) $this->inventory->available_quantity);

        $service->releaseOrder($order);

        $this->inventory->refresh();

        $this->assertSame(0, (int) $this->inventory->reserved_quantity);
        $this->assertSame(10, (int) $this->inventory->available_quantity);
    }

    public function test_releasing_reservation_does_not_reduce_physical_stock(): void
    {
        $order = $this->createOrder(4);

        $service = app(InventoryService::class);

        $service->reserveOrder($order);
        $service->releaseOrder($order);

        $this->inventory->refresh();

        $this->assertSame(10, (int) $this->inventory->quantity);
    }

    public function test_releasing_reservation_never_makes_reserved_quantity_negative(): void
    {
        $order = $this->createOrder(4);

        $this->inventory->update([
            'reserved_quantity' => 2,
        ]);

        app(InventoryService::class)->releaseOrder($order);

        $this->inventory->refresh();

        $this->assertSame(0, (int) $this->inventory->reserved_quantity);
    }

    public function test_multiple_orders_can_reserve_available_stock(): void
    {
        $firstOrder = $this->createOrder(3);
        $secondOrder = $this->createOrder(4);

        $service = app(InventoryService::class);

        $service->reserveOrder($firstOrder);
        $service->reserveOrder($secondOrder);

        $this->inventory->refresh();

        $this->assertSame(7, (int) $this->inventory->reserved_quantity);
        $this->assertSame(3, (int) $this->inventory->available_quantity);
    }

    
public function test_reservation_uses_base_quantity(): void
{
    $order = $this->createOrder(2);

    // This product is sold as packs of 10 pieces.
    $this->product->update([
        'selling_unit' => 'pack',
        'base_unit' => 'piece',
        'units_per_selling_unit' => 10,
    ]);

    // We need at least 20 base units for 2 packs.
    $this->inventory->update([
        'quantity' => 30,
        'reserved_quantity' => 0,
    ]);

    $item = $order->items->first();

    $item->update([
        'quantity' => 2,
        'selling_unit' => 'pack',
        'base_unit' => 'piece',
        'base_quantity' => 20,
    ]);

    $order->refresh()->load('items.product');

    app(InventoryService::class)->reserveOrder($order);

    $this->inventory->refresh();

    // Physical stock remains unchanged.
    $this->assertSame(30, (int) $this->inventory->quantity);

    // Reservation uses 20 BASE UNITS, not 2 selling units.
    $this->assertSame(20, (int) $this->inventory->reserved_quantity);

    // 30 - 20 = 10 base units available.
    $this->assertSame(10, (int) $this->inventory->available_quantity);
}

public function test_reservation_is_atomic_when_one_item_has_insufficient_stock(): void
{
    $secondProduct = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Test Cetirizine 10mg',
        'slug' => 'test-cetirizine-10mg-' . fake()->unique()->randomNumber(6),
        'sku' => 'TEST-CET-' . fake()->unique()->randomNumber(6),
        'price' => 800,
        'cost_price' => 400,
        'dosage_form' => 'Tablet',
        'strength' => '10mg',
        'base_unit' => 'piece',
        'selling_unit' => 'piece',
        'units_per_selling_unit' => 1,
        'allow_partial_sale' => false,
        'packaging_description' => null,
        'requires_prescription' => false,
        'is_active' => true,
        'is_featured' => false,
        'image' => null,
        'minimum_stock' => 1,
    ]);

    $secondInventory = Inventory::create([
        'product_id' => $secondProduct->id,
        'quantity' => 3,
        'reserved_quantity' => 0,
        'minimum_stock' => 1,
    ]);

    $order = Order::create([
        'order_number' => 'TEST-ATOMIC-' . strtoupper(fake()->unique()->bothify('######')),
        'customer_name' => 'Atomic Reservation Test',
        'customer_email' => 'atomic@example.com',
        'customer_phone' => '08000000000',
        'delivery_address' => 'Test Address',
        'subtotal' => 3500,
        'delivery_fee' => 0,
        'discount' => 0,
        'total' => 3500,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);

    $order->items()->create([
        'product_id' => $this->product->id,
        'product_name' => $this->product->name,
        'product_sku' => $this->product->sku,
        'unit_price' => 500,
        'quantity' => 2,
        'selling_unit' => 'piece',
        'base_unit' => 'piece',
        'base_quantity' => 2,
        'subtotal' => 1000,
    ]);

    $order->items()->create([
        'product_id' => $secondProduct->id,
        'product_name' => $secondProduct->name,
        'product_sku' => $secondProduct->sku,
        'unit_price' => 500,
        'quantity' => 5,
        'selling_unit' => 'piece',
        'base_unit' => 'piece',
        'base_quantity' => 5,
        'subtotal' => 2500,
    ]);

    $order->load('items.product');

    $service = app(InventoryService::class);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(
        'Insufficient stock for Test Cetirizine 10mg.'
    );

    $service->reserveOrder($order);

    $this->inventory->refresh();
    $secondInventory->refresh();

    // First item must NOT remain reserved.
    $this->assertSame(0, (int) $this->inventory->reserved_quantity);

    // Second inventory must also remain unchanged.
    $this->assertSame(0, (int) $secondInventory->reserved_quantity);

    // Physical stock must remain untouched.
    $this->assertSame(10, (int) $this->inventory->quantity);
    $this->assertSame(3, (int) $secondInventory->quantity);
}


}
