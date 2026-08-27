<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class InventoryStockTest extends TestCase
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

    public function test_add_stock_increases_physical_quantity(): void
    {
        app(\App\Services\InventoryService::class)->addStock(
            $this->inventory,
            5
        );

        $this->inventory->refresh();

        $this->assertSame(15, (int) $this->inventory->quantity);
    }

    public function test_add_stock_creates_inventory_transaction(): void
    {
        app(\App\Services\InventoryService::class)->addStock(
            $this->inventory,
            5,
            'purchase',
            'TEST-PURCHASE-001',
            'Test stock addition'
        );

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'inventory_id' => $this->inventory->id,
            'type' => 'purchase',
            'quantity' => 5,
            'quantity_before' => 10,
            'quantity_after' => 15,
            'reference' => 'TEST-PURCHASE-001',
        ]);
    }

    public function test_add_stock_does_not_change_reserved_quantity(): void
    {
        $this->inventory->update([
            'reserved_quantity' => 3,
        ]);

        app(\App\Services\InventoryService::class)->addStock(
            $this->inventory,
            5
        );

        $this->inventory->refresh();

        $this->assertSame(15, (int) $this->inventory->quantity);
        $this->assertSame(3, (int) $this->inventory->reserved_quantity);
    }

    public function test_add_stock_rejects_zero_quantity(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Stock quantity must be greater than zero.'
        );

        app(\App\Services\InventoryService::class)->addStock(
            $this->inventory,
            0
        );
    }

    public function test_remove_stock_decreases_physical_quantity(): void
    {
        app(\App\Services\InventoryService::class)->removeStock(
            $this->inventory,
            4
        );

        $this->inventory->refresh();

        $this->assertSame(6, (int) $this->inventory->quantity);
    }

    public function test_remove_stock_creates_inventory_transaction(): void
    {
        app(\App\Services\InventoryService::class)->removeStock(
            $this->inventory,
            4,
            'adjustment',
            'TEST-ADJUSTMENT-001',
            'Test stock removal'
        );

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'inventory_id' => $this->inventory->id,
            'type' => 'adjustment',
            'quantity' => -4,
            'quantity_before' => 10,
            'quantity_after' => 6,
            'reference' => 'TEST-ADJUSTMENT-001',
        ]);
    }

    public function test_remove_stock_cannot_remove_reserved_stock(): void
    {
        $this->inventory->update([
            'reserved_quantity' => 7,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Cannot remove stock that is currently reserved or unavailable.'
        );

        app(\App\Services\InventoryService::class)->removeStock(
            $this->inventory,
            4
        );

        $this->inventory->refresh();

        $this->assertSame(10, (int) $this->inventory->quantity);
    }

    public function test_remove_stock_rejects_quantity_greater_than_available_stock(): void
    {
        $this->inventory->update([
            'reserved_quantity' => 3,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Cannot remove stock that is currently reserved or unavailable.'
        );

        app(\App\Services\InventoryService::class)->removeStock(
            $this->inventory,
            8
        );
    }

    public function test_remove_stock_rejects_zero_quantity(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Stock quantity must be greater than zero.'
        );

        app(\App\Services\InventoryService::class)->removeStock(
            $this->inventory,
            0
        );
    }
}
