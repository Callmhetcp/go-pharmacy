<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected Category $category;

    protected Product $product;

    protected Inventory $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->category = Category::create([
            'name' => 'Checkout Test Category',
            'slug' => 'checkout-test-' . Str::random(8),
            'description' => 'Category used for checkout tests.',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Checkout Test Paracetamol',
            'slug' => 'checkout-paracetamol-' . Str::random(8),
            'sku' => 'CHECKOUT-PARA-' . Str::upper(Str::random(6)),
            'price' => 2000,
            'cost_price' => 1000,
            'dosage_form' => 'Tablet',
            'strength' => '500mg',
            'base_unit' => 'tablet',
            'selling_unit' => 'pack',
            'units_per_selling_unit' => 10,
            'allow_partial_sale' => false,
            'requires_prescription' => false,
            'is_active' => true,
            'is_featured' => false,
            'minimum_stock' => 10,
        ]);

        $this->inventory = Inventory::create([
            'product_id' => $this->product->id,
            'quantity' => 100,
            'reserved_quantity' => 0,
            'minimum_stock' => 10,
        ]);
    }

    protected function cart(int $quantity = 2): array
    {
        return [
            (string) $this->product->id => [
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'product_sku' => $this->product->sku,
                'unit_price' => 2000,
                'quantity' => $quantity,
                'subtotal' => 2000 * $quantity,
                'image' => $this->product->image,
                'base_unit' => 'tablet',
                'selling_unit' => 'pack',
                'units_per_selling_unit' => 10,
                'packaging_description' =>
                    $this->product->packaging_description,
                'base_quantity' => $quantity * 10,
            ],
        ];
    }

    public function test_customer_can_create_order_from_cart(): void
    {
        $response = $this
            ->actingAs($this->customer)
            ->withSession([
                'cart' => $this->cart(2),
            ])
            ->post(route('checkout.store'), [
                'customer_name' => 'Checkout Test Customer',
                'customer_email' => 'checkout@test.com',
                'customer_phone' => '08000000000',
                'delivery_address' => 'Test Address',
                'delivery_city' => 'Port Harcourt',
                'delivery_state' => 'Rivers',
            ]);

        $order = Order::query()
            ->where('user_id', $this->customer->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($order);

        $response->assertRedirect(
            route('payments.create', $order)
        );

        $response->assertSessionHas(
            'success',
            'Your order has been created successfully.'
        );
    }

    public function test_checkout_creates_pending_unpaid_order(): void
    {
        $this
            ->actingAs($this->customer)
            ->withSession([
                'cart' => $this->cart(2),
            ])
            ->post(route('checkout.store'), [
                'customer_name' => 'Checkout Test Customer',
                'customer_email' => 'checkout@test.com',
            ]);

        $order = Order::query()->latest('id')->first();

        $this->assertNotNull($order);

        $this->assertSame(
            'pending',
            $order->status
        );

        $this->assertSame(
            'unpaid',
            $order->payment_status
        );
    }

    public function test_checkout_creates_correct_order_item_and_base_quantity(): void
    {
        $this
            ->actingAs($this->customer)
            ->withSession([
                'cart' => $this->cart(2),
            ])
            ->post(route('checkout.store'), [
                'customer_name' => 'Checkout Test Customer',
                'customer_email' => 'checkout@test.com',
            ]);

        $order = Order::query()
            ->with('items')
            ->latest('id')
            ->first();

        $this->assertNotNull($order);

        $item = $order->items->first();

        $this->assertNotNull($item);

        $this->assertSame(
            2,
            (int) $item->quantity
        );

        $this->assertSame(
            20,
            (int) $item->base_quantity
        );

        $this->assertSame(
            'pack',
            $item->selling_unit
        );

        $this->assertSame(
            'tablet',
            $item->base_unit
        );

        $this->assertSame(
            2000.0,
            (float) $item->unit_price
        );

        $this->assertSame(
            4000.0,
            (float) $item->subtotal
        );
    }

    public function test_checkout_reserves_base_inventory_without_reducing_physical_stock(): void
    {
        $this
            ->actingAs($this->customer)
            ->withSession([
                'cart' => $this->cart(2),
            ])
            ->post(route('checkout.store'), [
                'customer_name' => 'Checkout Test Customer',
                'customer_email' => 'checkout@test.com',
            ]);

        $this->inventory->refresh();

        $this->assertSame(
            100,
            (int) $this->inventory->quantity
        );

        $this->assertSame(
            20,
            (int) $this->inventory->reserved_quantity
        );

        $this->assertSame(
            80,
            (int) $this->inventory->available_quantity
        );
    }

    public function test_successful_checkout_clears_cart(): void
    {
        $response = $this
            ->actingAs($this->customer)
            ->withSession([
                'cart' => $this->cart(2),
            ])
            ->post(route('checkout.store'), [
                'customer_name' => 'Checkout Test Customer',
                'customer_email' => 'checkout@test.com',
            ]);

        $response->assertSessionMissing('cart');
    }

    public function test_checkout_does_not_create_payment_record(): void
    {
        $this
            ->actingAs($this->customer)
            ->withSession([
                'cart' => $this->cart(2),
            ])
            ->post(route('checkout.store'), [
                'customer_name' => 'Checkout Test Customer',
                'customer_email' => 'checkout@test.com',
            ]);

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_empty_cart_cannot_create_order(): void
    {
        $response = $this
            ->actingAs($this->customer)
            ->withSession([
                'cart' => [],
            ])
            ->post(route('checkout.store'), [
                'customer_name' => 'Checkout Test Customer',
                'customer_email' => 'checkout@test.com',
            ]);

        $response
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas(
                'error',
                'Your cart is empty.'
            );

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_insufficient_stock_does_not_create_partial_order(): void
    {
        $this->inventory->update([
            'quantity' => 15,
            'reserved_quantity' => 0,
        ]);

        $response = $this
            ->actingAs($this->customer)
            ->withSession([
                'cart' => $this->cart(2),
            ])
            ->post(route('checkout.store'), [
                'customer_name' => 'Checkout Test Customer',
                'customer_email' => 'checkout@test.com',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseCount('orders', 0);

        $this->assertDatabaseCount(
            'order_items',
            0
        );

        $this->inventory->refresh();

        $this->assertSame(
            15,
            (int) $this->inventory->quantity
        );

        $this->assertSame(
            0,
            (int) $this->inventory->reserved_quantity
        );
    }
}
