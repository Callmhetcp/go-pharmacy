<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class PosInventoryTest extends TestCase
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
            'name' => 'Test Medicines',
            'slug' => 'test-medicines-' . Str::lower(Str::random(6)),
            'description' => 'Test category.',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Test Paracetamol 500mg',
            'slug' => 'test-paracetamol-500mg-' . Str::lower(Str::random(6)),
            'sku' => 'TEST-PARA-' . strtoupper(Str::random(6)),
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
    }

    protected function createPosOrder(
        int $quantity = 1
    ): Order {
        $order = Order::create([
            'order_number' => 'GP-POS-TEST-' . strtoupper(Str::random(8)),
            'customer_name' => 'POS Test Customer',
            'customer_email' => 'pos-test@gopharmacy.test',
            'customer_phone' => '08000000001',
            'delivery_address' => null,
            'subtotal' => 500 * $quantity,
            'delivery_fee' => 0,
            'discount' => 0,
            'total' => 500 * $quantity,
            'status' => 'processing',
            'payment_status' => 'paid',
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
            'subtotal' => 500 * $quantity,
        ]);

        return $order->fresh('items.product');
    }

    public function test_pos_sale_deducts_physical_stock(): void
    {
        $order = $this->createPosOrder(2);

        $this->actingAs($this->admin);

        app(InventoryService::class)->fulfillPosSale($order);

        $inventory = $this->inventory->fresh();

        $this->assertSame(8, (int) $inventory->quantity);
        $this->assertSame(0, (int) $inventory->reserved_quantity);
    }

    public function test_pos_sale_creates_inventory_transaction(): void
    {
        $order = $this->createPosOrder(2);

        $this->actingAs($this->admin);

        app(InventoryService::class)->fulfillPosSale($order);

        $transaction = InventoryTransaction::query()
            ->where('reference', $order->order_number)
            ->first();

        $this->assertNotNull($transaction);
        $this->assertSame('pos_sale', $transaction->type);
        $this->assertSame(-2, (int) $transaction->quantity);
        $this->assertSame(10, (int) $transaction->quantity_before);
        $this->assertSame(8, (int) $transaction->quantity_after);
        $this->assertSame($this->product->id, $transaction->product_id);
    }

    public function test_pos_sale_does_not_reduce_reserved_quantity(): void
    {
        $this->inventory->update([
            'reserved_quantity' => 4,
        ]);

        $order = $this->createPosOrder(2);

        $this->actingAs($this->admin);

        app(InventoryService::class)->fulfillPosSale($order);

        $inventory = $this->inventory->fresh();

        $this->assertSame(8, (int) $inventory->quantity);
        $this->assertSame(4, (int) $inventory->reserved_quantity);
    }

    public function test_pos_sale_cannot_use_reserved_stock(): void
    {
        $this->inventory->update([
            'reserved_quantity' => 8,
        ]);

        $order = $this->createPosOrder(3);

        $this->actingAs($this->admin);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Insufficient stock for'
        );

        app(InventoryService::class)->fulfillPosSale($order);
    }

    public function test_pos_sale_does_not_oversell_available_stock(): void
    {
        $this->inventory->update([
            'quantity' => 5,
            'reserved_quantity' => 2,
        ]);

        $order = $this->createPosOrder(4);

        $this->actingAs($this->admin);

        $this->expectException(RuntimeException::class);

        app(InventoryService::class)->fulfillPosSale($order);

        $inventory = $this->inventory->fresh();

        $this->assertSame(5, (int) $inventory->quantity);
        $this->assertSame(2, (int) $inventory->reserved_quantity);
    }

public function test_pos_sale_is_atomic_when_one_item_has_insufficient_stock(): void
{
    $secondProduct = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Second Test Medicine',
        'slug' => 'second-test-medicine-' . Str::lower(Str::random(6)),
        'sku' => 'TEST-SECOND-' . strtoupper(Str::random(6)),
        'price' => 700,
        'cost_price' => 350,
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

    $secondInventory = Inventory::create([
        'product_id' => $secondProduct->id,
        'quantity' => 1,
        'reserved_quantity' => 0,
        'minimum_stock' => 1,
    ]);

    $order = Order::create([
        'order_number' => 'GP-POS-ATOMIC-' . strtoupper(Str::random(8)),
        'customer_name' => 'POS Atomic Test',
        'customer_email' => 'pos-atomic@gopharmacy.test',
        'customer_phone' => '08000000002',
        'delivery_address' => null,
        'subtotal' => 1500,
        'delivery_fee' => 0,
        'discount' => 0,
        'total' => 1500,
        'status' => 'processing',
        'payment_status' => 'paid',
    ]);

    $order->items()->create([
        'product_id' => $this->product->id,
        'product_name' => $this->product->name,
        'product_sku' => $this->product->sku,
        'unit_price' => 500,
        'quantity' => 1,
        'selling_unit' => 'piece',
        'base_unit' => 'piece',
        'base_quantity' => 1,
        'subtotal' => 500,
    ]);

    $order->items()->create([
        'product_id' => $secondProduct->id,
        'product_name' => $secondProduct->name,
        'product_sku' => $secondProduct->sku,
        'unit_price' => 700,
        'quantity' => 2,
        'selling_unit' => 'piece',
        'base_unit' => 'piece',
        'base_quantity' => 2,
        'subtotal' => 1400,
    ]);

    $order->refresh()->load('items.product');

    $this->actingAs($this->admin);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Insufficient stock for');

    app(InventoryService::class)->fulfillPosSale($order);

    $this->assertSame(
        10,
        (int) $this->inventory->fresh()->quantity
    );

    $this->assertSame(
        1,
        (int) $secondInventory->fresh()->quantity
    );

    $this->assertSame(
        0,
        InventoryTransaction::where(
            'reference',
            $order->order_number
        )->count()
    );
}

}
