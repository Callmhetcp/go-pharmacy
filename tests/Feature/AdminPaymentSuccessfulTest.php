<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use RuntimeException;

class AdminPaymentSuccessfulTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected Category $category;

    protected Product $product;

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
            'name' => 'Test Medicines',
            'slug' => 'test-medicines-' . Str::lower(Str::random(6)),
            'description' => 'Category used for automated tests.',
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

        Inventory::create([
            'product_id' => $this->product->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
            'minimum_stock' => 1,
        ]);
    }

    protected function createOrder(): Order
    {
        $order = Order::create([
            'order_number' => 'GP-TEST-' . strtoupper(Str::random(8)),
            'user_id' => $this->customer->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@gopharmacy.test',
            'customer_phone' => '08000000000',
            'delivery_address' => 'Test Address',
            'delivery_city' => 'Port Harcourt',
            'delivery_state' => 'Rivers',
            'subtotal' => 500,
            'delivery_fee' => 0,
            'discount' => 0,
            'total' => 500,
            'status' => 'pending',
            'payment_status' => 'unpaid',
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

        return $order->fresh('items');
    }

    protected function createPayment(Order $order): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'payment_reference' => 'GP-TEST-PAY-' . strtoupper(Str::random(8)),
            'gateway' => 'pending',
            'amount' => 500,
            'currency' => 'NGN',
            'status' => 'pending',
        ]);
    }

    public function test_authenticated_admin_can_mark_payment_as_successful_and_fulfill_order(): void
    {
        $order = $this->createOrder();
        $payment = $this->createPayment($order);

        app(\App\Services\InventoryService::class)
            ->reserveOrder($order);

        $response = $this
            ->actingAs($this->admin)
            ->postJson(
                route('api.admin.payments.successful', $payment)
            );

        $response->assertSuccessful();

        $payment->refresh();
        $order->refresh();

        $inventory = Inventory::where(
            'product_id',
            $this->product->id
        )->firstOrFail();

        $this->assertSame(
            'successful',
            $payment->status
        );

        $this->assertSame(
            'paid',
            $order->payment_status
        );

        $this->assertSame(
            'processing',
            $order->status
        );

        $this->assertSame(
            9,
            $inventory->quantity
        );

        $this->assertSame(
            0,
            $inventory->reserved_quantity
        );

        $this->assertSame(
            1,
            \App\Models\InventoryTransaction::where(
                'reference',
                $order->order_number
            )->count()
        );
    }

    public function test_authenticated_non_admin_cannot_mark_payment_as_successful(): void
    {
        $order = $this->createOrder();
        $payment = $this->createPayment($order);

        app(\App\Services\InventoryService::class)
            ->reserveOrder($order);

        $response = $this
            ->actingAs($this->customer)
            ->postJson(
                route('api.admin.payments.successful', $payment)
            );

        $response->assertForbidden();

        $payment->refresh();
        $order->refresh();

        $this->assertSame(
            'pending',
            $payment->status
        );

        $this->assertSame(
            'unpaid',
            $order->payment_status
        );

        $this->assertSame(
            'pending',
            $order->status
        );
    }

    public function test_unauthenticated_user_cannot_mark_payment_as_successful(): void
    {
        $order = $this->createOrder();
        $payment = $this->createPayment($order);

        app(\App\Services\InventoryService::class)
            ->reserveOrder($order);

        $response = $this->postJson(
            route('api.admin.payments.successful', $payment)
        );

        $response->assertUnauthorized();

        $payment->refresh();
        $order->refresh();

        $this->assertSame(
            'pending',
            $payment->status
        );

        $this->assertSame(
            'unpaid',
            $order->payment_status
        );

        $this->assertSame(
            'pending',
            $order->status
        );
    }

    public function test_order_cannot_be_fulfilled_twice(): void
    {
        $order = $this->createOrder();
        $payment = $this->createPayment($order);

        app(\App\Services\InventoryService::class)
            ->reserveOrder($order);

        $this
            ->actingAs($this->admin)
            ->postJson(
                route('api.admin.payments.successful', $payment)
            )
            ->assertSuccessful();

        $order->refresh();

        $inventoryBefore = Inventory::where(
            'product_id',
            $this->product->id
        )->firstOrFail();

        $transactionCountBefore =
            \App\Models\InventoryTransaction::where(
                'reference',
                $order->order_number
            )->count();

        $this->expectException(\RuntimeException::class);

        $this->expectExceptionMessage(
            'This order has already been fulfilled and cannot be fulfilled again.'
        );

        app(\App\Services\InventoryService::class)
            ->fulfillOrder($order);

        $inventoryAfter = Inventory::where(
            'product_id',
            $this->product->id
        )->firstOrFail();

        $transactionCountAfter =
            \App\Models\InventoryTransaction::where(
                'reference',
                $order->order_number
            )->count();

        $this->assertSame(
            $inventoryBefore->quantity,
            $inventoryAfter->quantity
        );

        $this->assertSame(
            $transactionCountBefore,
            $transactionCountAfter
        );
    }


    public function test_authenticated_admin_can_mark_payment_as_failed_without_deducting_inventory(): void
{
    $order = $this->createOrder();
    $payment = $this->createPayment($order);

    app(\App\Services\InventoryService::class)
        ->reserveOrder($order);

    $inventoryBefore = Inventory::where(
        'product_id',
        $this->product->id
    )->firstOrFail();

    $transactionCountBefore =
        \App\Models\InventoryTransaction::where(
            'reference',
            $order->order_number
        )->count();

    $response = $this
        ->actingAs($this->admin)
        ->postJson(
            route('api.admin.payments.failed', $payment)
        );

    $response->assertSuccessful();

    $payment->refresh();
    $order->refresh();

    $inventoryAfter = Inventory::where(
        'product_id',
        $this->product->id
    )->firstOrFail();

    $transactionCountAfter =
        \App\Models\InventoryTransaction::where(
            'reference',
            $order->order_number
        )->count();

    $this->assertSame(
        'failed',
        $payment->status
    );

    $this->assertSame(
        'failed',
        $order->payment_status
    );

    $this->assertSame(
        'pending',
        $order->status
    );

    $this->assertSame(
        $inventoryBefore->quantity,
        $inventoryAfter->quantity
    );

    $this->assertSame(
        $transactionCountBefore,
        $transactionCountAfter
    );

   $this->assertSame(
    0,
    $inventoryAfter->reserved_quantity
);

$this->assertSame(
    10,
    $inventoryAfter->available_quantity
);
}

public function test_fulfillment_is_atomic_when_one_item_has_insufficient_stock(): void
{
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category-' . Str::random(8),
        'description' => 'Test category',
        'is_active' => true,
    ]);

    $productOne = Product::create([
        'category_id' => $category->id,
        'name' => 'Atomic Test Product One',
        'slug' => 'atomic-product-one-' . Str::random(8),
        'sku' => 'ATOMIC-ONE-' . Str::upper(Str::random(6)),
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

    $productTwo = Product::create([
        'category_id' => $category->id,
        'name' => 'Atomic Test Product Two',
        'slug' => 'atomic-product-two-' . Str::random(8),
        'sku' => 'ATOMIC-TWO-' . Str::upper(Str::random(6)),
        'price' => 700,
        'cost_price' => 350,
        'dosage_form' => 'Tablet',
        'strength' => '250mg',
        'base_unit' => 'piece',
        'selling_unit' => 'piece',
        'units_per_selling_unit' => 1,
        'allow_partial_sale' => false,
        'requires_prescription' => false,
        'is_active' => true,
        'is_featured' => false,
        'minimum_stock' => 1,
    ]);

    $inventoryOne = Inventory::create([
        'product_id' => $productOne->id,
        'quantity' => 10,
        'reserved_quantity' => 0,
        'minimum_stock' => 1,
    ]);

    $inventoryTwo = Inventory::create([
        'product_id' => $productTwo->id,
        'quantity' => 0,
        'reserved_quantity' => 0,
        'minimum_stock' => 1,
    ]);

    $order = Order::create([
        'order_number' => 'GP-ATOMIC-' . Str::upper(Str::random(8)),
        'customer_name' => 'Atomic Test Customer',
        'customer_email' => 'atomic@test.com',
        'customer_phone' => '08000000000',
        'delivery_address' => 'Atomic Test Address',
        'subtotal' => 1200,
        'delivery_fee' => 0,
        'discount' => 0,
        'total' => 1200,
        'status' => 'pending',
        'payment_status' => 'paid',
    ]);

    $order->items()->create([
        'product_id' => $productOne->id,
        'product_name' => $productOne->name,
        'product_sku' => $productOne->sku,
        'unit_price' => 500,
        'quantity' => 1,
        'selling_unit' => 'piece',
        'base_unit' => 'piece',
        'base_quantity' => 1,
        'subtotal' => 500,
    ]);

    $order->items()->create([
        'product_id' => $productTwo->id,
        'product_name' => $productTwo->name,
        'product_sku' => $productTwo->sku,
        'unit_price' => 700,
        'quantity' => 1,
        'selling_unit' => 'piece',
        'base_unit' => 'piece',
        'base_quantity' => 1,
        'subtotal' => 700,
    ]);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage(
        'Insufficient physical stock for ' . $productTwo->name . '.'
    );

    app(InventoryService::class)->fulfillOrder(
        $order->fresh('items')
    );

    $inventoryOne->refresh();
    $inventoryTwo->refresh();

    $this->assertSame(10, $inventoryOne->quantity);
    $this->assertSame(0, $inventoryOne->reserved_quantity);

    $this->assertSame(0, $inventoryTwo->quantity);
    $this->assertSame(0, $inventoryTwo->reserved_quantity);

    $this->assertDatabaseMissing('inventory_transactions', [
        'reference' => $order->order_number,
    ]);
}
public function test_failed_payment_releases_order_reservation_without_reducing_stock(): void
{
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $category = Category::create([
        'name' => 'Reservation Test Category',
        'slug' => 'reservation-test-' . Str::random(8),
        'description' => 'Reservation test category',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Reservation Test Paracetamol',
        'slug' => 'reservation-paracetamol-' . Str::random(8),
        'sku' => 'RES-PARA-' . Str::upper(Str::random(6)),
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

    $inventory = Inventory::create([
        'product_id' => $product->id,
        'quantity' => 10,
        'reserved_quantity' => 0,
        'minimum_stock' => 1,
    ]);

    $order = Order::create([
        'order_number' => 'GP-RES-FAIL-' . Str::upper(Str::random(8)),
        'customer_name' => 'Reservation Test Customer',
        'customer_email' => 'reservation-fail@test.com',
        'customer_phone' => '08000000001',
        'delivery_address' => 'Reservation Test Address',
        'subtotal' => 500,
        'delivery_fee' => 0,
        'discount' => 0,
        'total' => 500,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_sku' => $product->sku,
        'unit_price' => 500,
        'quantity' => 2,
        'selling_unit' => 'piece',
        'base_unit' => 'piece',
        'base_quantity' => 2,
        'subtotal' => 1000,
    ]);

    $order = $order->fresh('items');

    app(InventoryService::class)->reserveOrder($order);

    $inventory->refresh();

    $this->assertSame(10, $inventory->quantity);
    $this->assertSame(2, $inventory->reserved_quantity);
    $this->assertSame(8, $inventory->available_quantity);

    app(InventoryService::class)->releaseOrder($order);

    $inventory->refresh();

    $this->assertSame(10, $inventory->quantity);
    $this->assertSame(0, $inventory->reserved_quantity);
    $this->assertSame(10, $inventory->available_quantity);
}
public function test_successful_payment_consumes_reserved_stock_and_clears_reservation(): void
{
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $category = Category::create([
        'name' => 'Successful Reservation Category',
        'slug' => 'successful-reservation-' . Str::random(8),
        'description' => 'Successful reservation test category',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Successful Reservation Product',
        'slug' => 'successful-reservation-product-' . Str::random(8),
        'sku' => 'SUCCESS-RES-' . Str::upper(Str::random(6)),
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

    $inventory = Inventory::create([
        'product_id' => $product->id,
        'quantity' => 10,
        'reserved_quantity' => 0,
        'minimum_stock' => 1,
    ]);

    $order = Order::create([
        'order_number' => 'GP-RES-SUCCESS-' . Str::upper(Str::random(8)),
        'customer_name' => 'Successful Reservation Customer',
        'customer_email' => 'reservation-success@test.com',
        'customer_phone' => '08000000002',
        'delivery_address' => 'Successful Reservation Address',
        'subtotal' => 1000,
        'delivery_fee' => 0,
        'discount' => 0,
        'total' => 1000,
        'status' => 'pending',
        'payment_status' => 'paid',
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_sku' => $product->sku,
        'unit_price' => 500,
        'quantity' => 2,
        'selling_unit' => 'piece',
        'base_unit' => 'piece',
        'base_quantity' => 2,
        'subtotal' => 1000,
    ]);

    $order = $order->fresh('items');

    app(InventoryService::class)->reserveOrder($order);

    $inventory->refresh();

    $this->assertSame(10, $inventory->quantity);
    $this->assertSame(2, $inventory->reserved_quantity);
    $this->assertSame(8, $inventory->available_quantity);

    app(InventoryService::class)->fulfillOrder($order);

    $inventory->refresh();
    $order->refresh();

    $this->assertSame(8, $inventory->quantity);
    $this->assertSame(0, $inventory->reserved_quantity);
    $this->assertSame(8, $inventory->available_quantity);

    $this->assertSame('processing', $order->status);

    $this->assertDatabaseHas('inventory_transactions', [
        'product_id' => $product->id,
        'inventory_id' => $inventory->id,
        'type' => 'online_sale',
        'quantity' => -2,
        'reference' => $order->order_number,
    ]);
}

public function test_authenticated_admin_can_mark_payment_as_failed_and_release_reservation(): void
{
    $order = $this->createOrder();
    $payment = $this->createPayment($order);

    app(InventoryService::class)->reserveOrder($order);

    $inventory = Inventory::where(
        'product_id',
        $this->product->id
    )->firstOrFail();

    $this->assertSame(10, $inventory->quantity);
    $this->assertSame(1, $inventory->reserved_quantity);
    $this->assertSame(9, $inventory->available_quantity);

    $response = $this
        ->actingAs($this->admin)
        ->postJson(
            route('api.admin.payments.failed', $payment)
        );

    $response->assertSuccessful();

    $payment->refresh();
    $order->refresh();
    $inventory->refresh();

    /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    */

    $this->assertSame(
        'failed',
        $payment->status
    );

    /*
    |--------------------------------------------------------------------------
    | Order
    |--------------------------------------------------------------------------
    */

    $this->assertSame(
        'failed',
        $order->payment_status
    );

    $this->assertSame(
        'pending',
        $order->status
    );

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    |
    | Physical stock must NOT be reduced.
    | Reservation must be released.
    |
    */

    $this->assertSame(
        10,
        $inventory->quantity
    );

    $this->assertSame(
        0,
        $inventory->reserved_quantity
    );

    $this->assertSame(
        10,
        $inventory->available_quantity
    );

    /*
    |--------------------------------------------------------------------------
    | Inventory transactions
    |--------------------------------------------------------------------------
    |
    | A failed payment must not create a sale transaction.
    |
    */

    $this->assertDatabaseMissing(
        'inventory_transactions',
        [
            'reference' => $order->order_number,
        ]
    );
}
}