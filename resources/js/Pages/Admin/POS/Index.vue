<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    paymentMethods: {
        type: Array,
        default: () => [],
    },
});

const productSearch = ref('');
const customerSearch = ref('');

const products = ref([]);
const customers = ref([]);
const cart = ref([]);

const selectedCustomer = ref(null);

const paymentMethod = ref('cash');
const discount = ref(0);

const searchingProducts = ref(false);
const searchingCustomers = ref(false);
const completingSale = ref(false);

let productSearchTimer = null;
let customerSearchTimer = null;

/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

const formatMoney = (value) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        maximumFractionDigits: 2,
    }).format(Number(value || 0));
};

/*
|--------------------------------------------------------------------------
| Totals
|--------------------------------------------------------------------------
*/

const subtotal = computed(() => {
    return cart.value.reduce(
        (total, item) =>
            total +
            Number(item.price) * Number(item.quantity),
        0
    );
});

const safeDiscount = computed(() => {
    return Math.min(
        Math.max(Number(discount.value || 0), 0),
        subtotal.value
    );
});

const total = computed(() => {
    return Math.max(
        0,
        subtotal.value - safeDiscount.value
    );
});

/*
|--------------------------------------------------------------------------
| Product Search
|--------------------------------------------------------------------------
*/

const searchProducts = () => {
    clearTimeout(productSearchTimer);

    const search = productSearch.value.trim();

    if (!search) {
        products.value = [];
        return;
    }

    productSearchTimer = setTimeout(async () => {
        searchingProducts.value = true;

        try {
            const response = await fetch(
                route('admin.pos.products', {
                    search,
                }),
                {
                    headers: {
                        Accept: 'application/json',
                    },
                }
            );

            if (!response.ok) {
                throw new Error(
                    'Product search request failed.'
                );
            }

            const data = await response.json();

            products.value = data.products ?? [];
        } catch (error) {
            console.error(
                'Product search failed:',
                error
            );
        } finally {
            searchingProducts.value = false;
        }
    }, 250);
};

/*
|--------------------------------------------------------------------------
| Customer Search
|--------------------------------------------------------------------------
*/

const searchCustomers = () => {
    clearTimeout(customerSearchTimer);

    const search = customerSearch.value.trim();

    if (!search) {
        customers.value = [];
        return;
    }

    customerSearchTimer = setTimeout(async () => {
        searchingCustomers.value = true;

        try {
            const response = await fetch(
                route('admin.pos.customers', {
                    search,
                }),
                {
                    headers: {
                        Accept: 'application/json',
                    },
                }
            );

            if (!response.ok) {
                throw new Error(
                    'Customer search request failed.'
                );
            }

            const data = await response.json();

            customers.value = data.customers ?? [];
        } catch (error) {
            console.error(
                'Customer search failed:',
                error
            );
        } finally {
            searchingCustomers.value = false;
        }
    }, 250);
};

/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/

const addProduct = (product) => {
    if (
        Number(product.available_selling_quantity) < 1
    ) {
        return;
    }

    const existing = cart.value.find(
        (item) => item.product_id === product.id
    );

    if (existing) {
        if (
            existing.quantity <
            Number(product.available_selling_quantity)
        ) {
            existing.quantity++;
        }

        return;
    }

    cart.value.push({
        product_id: product.id,
        name: product.name,
        sku: product.sku,
        price: Number(product.price),
        selling_unit: product.selling_unit,
        available_quantity:
            Number(product.available_selling_quantity),
        quantity: 1,
    });

    productSearch.value = '';
    products.value = [];
};

const increaseQuantity = (item) => {
    if (
        item.quantity <
        item.available_quantity
    ) {
        item.quantity++;
    }
};

const decreaseQuantity = (item) => {
    if (item.quantity > 1) {
        item.quantity--;
        return;
    }

    removeItem(item);
};

const removeItem = (item) => {
    cart.value = cart.value.filter(
        (cartItem) =>
            cartItem.product_id !== item.product_id
    );
};

/*
|--------------------------------------------------------------------------
| Customer
|--------------------------------------------------------------------------
*/

const selectCustomer = (customer) => {
    selectedCustomer.value = customer;

    customerSearch.value = '';
    customers.value = [];
};

const clearCustomer = () => {
    selectedCustomer.value = null;

    customerSearch.value = '';
    customers.value = [];
};

/*
|--------------------------------------------------------------------------
| New Sale
|--------------------------------------------------------------------------
*/

const clearCart = () => {
    cart.value = [];

    discount.value = 0;

    selectedCustomer.value = null;

    customerSearch.value = '';
    customers.value = [];

    productSearch.value = '';
    products.value = [];

    paymentMethod.value = 'cash';
};

/*
|--------------------------------------------------------------------------
| Complete Sale
|--------------------------------------------------------------------------
*/

const completeSale = () => {
    if (cart.value.length === 0) {
        return;
    }

    completingSale.value = true;

    router.post(
        route('admin.pos.store'),
        {
            items: cart.value.map((item) => ({
                product_id: item.product_id,
                quantity: item.quantity,
            })),

            customer_id:
                selectedCustomer.value?.id ?? null,

            customer_name:
                selectedCustomer.value?.name ??
                'Walk-in Customer',

            customer_email:
                selectedCustomer.value?.email ?? null,

            customer_phone:
                selectedCustomer.value?.phone ?? null,

            discount: safeDiscount.value,

            payment_method:
                paymentMethod.value,
        },
        {
            preserveScroll: true,

            onFinish: () => {
                completingSale.value = false;
            },
        }
    );
};
</script>

<template>
    <AdminLayout>
        <div class="admin-layout min-h-full">
            <!-- =====================================================
                 HEADER
            ====================================================== -->

            <section
                class="border-b border-slate-200 bg-white"
            >
                <div
                    class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-sm font-semibold text-green-600"
                            >
                                Admin Panel
                            </p>

                            <h1
                                class="mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl"
                            >
                                Point of Sale
                            </h1>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Sell products to customers.
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <Link
                                :href="route('admin.pos.history')"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                Sales History
                            </Link>

                            <button
                                type="button"
                                @click="clearCart"
                                class="rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700"
                            >
                                New Sale
                            </button>
                        </div>
                    </div>

                    <!-- Breadcrumbs -->

                    <nav
                        class="mt-5 flex items-center gap-2 text-sm"
                        aria-label="Breadcrumb"
                    >
                        <Link
                            :href="route('admin.dashboard')"
                            class="font-medium text-slate-500 transition hover:text-green-600"
                        >
                            Admin Dashboard
                        </Link>

                        <span class="text-slate-300">
                            /
                        </span>

                        <span
                            class="font-medium text-slate-500"
                        >
                            POS
                        </span>

                        <span class="text-slate-300">
                            /
                        </span>

                        <span
                            class="font-semibold text-slate-900"
                            aria-current="page"
                        >
                            New Sale
                        </span>
                    </nav>
                </div>
            </section>

            <!-- =====================================================
                 MAIN
            ====================================================== -->

            <main
                class="mx-auto max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8"
            >
                <div
                    class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_420px]"
                >
                    <!-- =================================================
                         PRODUCTS + CART
                    ================================================== -->

                    <section class="space-y-6">
                        <!-- Product Search -->

                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                        >
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-900"
                            >
                                Search Product
                            </label>

                            <div class="relative">
                                <input
                                    v-model="productSearch"
                                    @input="searchProducts"
                                    type="search"
                                    placeholder="Search name, SKU, barcode, brand..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-500/10"
                                />

                                <div
                                    v-if="searchingProducts"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"
                                >
                                    Searching...
                                </div>
                            </div>

                            <!-- Product Results -->

                            <div
                                v-if="products.length"
                                class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3"
                            >
                                <button
                                    v-for="product in products"
                                    :key="product.id"
                                    type="button"
                                    @click="addProduct(product)"
                                    :disabled="
                                        Number(
                                            product.available_selling_quantity
                                        ) < 1
                                    "
                                    class="rounded-2xl border border-slate-200 bg-white p-4 text-left transition hover:border-green-500 hover:shadow-sm disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <div
                                        class="font-semibold text-slate-900"
                                    >
                                        {{ product.name }}
                                    </div>

                                    <div
                                        class="mt-1 text-xs text-slate-500"
                                    >
                                        {{ product.sku || 'No SKU' }}
                                    </div>

                                    <div
                                        class="mt-3 flex items-end justify-between gap-3"
                                    >
                                        <div>
                                            <div
                                                class="font-bold text-green-600"
                                            >
                                                {{
                                                    formatMoney(
                                                        product.price
                                                    )
                                                }}
                                            </div>

                                            <div
                                                class="text-xs text-slate-500"
                                            >
                                                per
                                                {{
                                                    product.selling_unit
                                                }}
                                            </div>
                                        </div>

                                        <div
                                            class="text-right text-xs"
                                        >
                                            <div
                                                class="font-semibold text-slate-900"
                                            >
                                                {{
                                                    product.available_selling_quantity
                                                }}
                                            </div>

                                            <div
                                                class="text-slate-500"
                                            >
                                                available
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Current Sale -->

                        <div
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                        >
                            <div
                                class="flex items-center justify-between border-b border-slate-200 px-5 py-4"
                            >
                                <div>
                                    <h2
                                        class="font-bold text-slate-900"
                                    >
                                        Current Sale
                                    </h2>

                                    <p
                                        class="text-xs text-slate-500"
                                    >
                                        {{ cart.length }}
                                        product(s)
                                    </p>
                                </div>

                                <button
                                    v-if="cart.length"
                                    type="button"
                                    @click="clearCart"
                                    class="text-sm font-semibold text-red-600 hover:text-red-700"
                                >
                                    Clear
                                </button>
                            </div>

                            <!-- Empty Cart -->

                            <div
                                v-if="cart.length === 0"
                                class="px-5 py-16 text-center"
                            >
                                <div
                                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-400"
                                >
                                    🛒
                                </div>

                                <h3
                                    class="mt-4 font-bold text-slate-900"
                                >
                                    No products added
                                </h3>

                                <p
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    Search for a product above
                                    to start the sale.
                                </p>
                            </div>

                            <!-- Cart Items -->

                            <div
                                v-else
                                class="divide-y divide-slate-100"
                            >
                                <div
                                    v-for="item in cart"
                                    :key="item.product_id"
                                    class="flex items-center gap-4 px-5 py-4"
                                >
                                    <div
                                        class="min-w-0 flex-1"
                                    >
                                        <div
                                            class="truncate font-semibold text-slate-900"
                                        >
                                            {{ item.name }}
                                        </div>

                                        <div
                                            class="mt-1 text-xs text-slate-500"
                                        >
                                            {{
                                                formatMoney(
                                                    item.price
                                                )
                                            }}
                                            /
                                            {{
                                                item.selling_unit
                                            }}
                                        </div>
                                    </div>

                                    <!-- Quantity -->

                                    <div
                                        class="flex items-center rounded-xl border border-slate-200"
                                    >
                                        <button
                                            type="button"
                                            @click="
                                                decreaseQuantity(
                                                    item
                                                )
                                            "
                                            class="px-3 py-2 text-lg text-slate-700"
                                        >
                                            −
                                        </button>

                                        <span
                                            class="min-w-10 text-center text-sm font-bold text-slate-900"
                                        >
                                            {{ item.quantity }}
                                        </span>

                                        <button
                                            type="button"
                                            @click="
                                                increaseQuantity(
                                                    item
                                                )
                                            "
                                            class="px-3 py-2 text-lg text-slate-700"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <!-- Item Total -->

                                    <div
                                        class="w-28 text-right font-bold text-slate-900"
                                    >
                                        {{
                                            formatMoney(
                                                item.price *
                                                    item.quantity
                                            )
                                        }}
                                    </div>

                                    <!-- Remove -->

                                    <button
                                        type="button"
                                        @click="
                                            removeItem(item)
                                        "
                                        class="text-xl text-slate-400 transition hover:text-red-600"
                                        aria-label="Remove item"
                                    >
                                        ×
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- =================================================
                         CHECKOUT
                    ================================================== -->

                    <aside
                        class="h-fit rounded-2xl border border-slate-200 bg-white shadow-sm"
                    >
                        <div
                            class="border-b border-slate-200 px-5 py-4"
                        >
                            <h2
                                class="font-bold text-slate-900"
                            >
                                Checkout
                            </h2>

                            <p
                                class="text-xs text-slate-500"
                            >
                                Complete the sale.
                            </p>
                        </div>

                        <div class="space-y-6 p-5">
                            <!-- Customer -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-900"
                                >
                                    Customer
                                </label>

                                <!-- Selected Customer -->

                                <div
                                    v-if="selectedCustomer"
                                    class="flex items-center justify-between rounded-xl border border-green-200 bg-green-50 p-3"
                                >
                                    <div
                                        class="min-w-0"
                                    >
                                        <div
                                            class="truncate font-semibold text-green-800"
                                        >
                                            {{
                                                selectedCustomer.name
                                            }}
                                        </div>

                                        <div
                                            v-if="
                                                selectedCustomer.email
                                            "
                                            class="truncate text-xs text-green-700"
                                        >
                                            {{
                                                selectedCustomer.email
                                            }}
                                        </div>

                                        <div
                                            v-if="
                                                selectedCustomer.phone
                                            "
                                            class="text-xs text-green-700"
                                        >
                                            {{
                                                selectedCustomer.phone
                                            }}
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        @click="clearCustomer"
                                        class="ml-3 shrink-0 text-sm font-semibold text-red-600"
                                    >
                                        Change
                                    </button>
                                </div>

                                <!-- Customer Search -->

                                <template v-else>
                                    <input
                                        v-model="customerSearch"
                                        @input="searchCustomers"
                                        type="search"
                                        placeholder="Search existing customer..."
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-500/10"
                                    />

                                    <div
                                        v-if="
                                            searchingCustomers
                                        "
                                        class="mt-2 text-xs text-slate-400"
                                    >
                                        Searching customers...
                                    </div>

                                    <!-- Customer Results -->

                                    <div
                                        v-if="
                                            customers.length
                                        "
                                        class="mt-2 max-h-64 overflow-y-auto overflow-hidden rounded-xl border border-slate-200"
                                    >
                                        <button
                                            v-for="customer in customers"
                                            :key="customer.id"
                                            type="button"
                                            @click="
                                                selectCustomer(
                                                    customer
                                                )
                                            "
                                            class="block w-full border-b border-slate-100 px-4 py-3 text-left last:border-0 hover:bg-slate-50"
                                        >
                                            <div
                                                class="text-sm font-semibold text-slate-900"
                                            >
                                                {{
                                                    customer.name
                                                }}
                                            </div>

                                            <div
                                                v-if="
                                                    customer.email
                                                "
                                                class="text-xs text-slate-500"
                                            >
                                                {{
                                                    customer.email
                                                }}
                                            </div>

                                            <div
                                                v-if="
                                                    customer.phone
                                                "
                                                class="text-xs text-slate-500"
                                            >
                                                {{
                                                    customer.phone
                                                }}
                                            </div>
                                        </button>
                                    </div>

                                    <!-- Walk-in Indicator -->

                                    <div
                                        class="mt-3 flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2.5 text-xs text-slate-500"
                                    >
                                        <span
                                            class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-200"
                                        >
                                            👤
                                        </span>

                                        <span>
                                            No customer selected —
                                            <strong
                                                class="text-slate-700"
                                            >
                                                Walk-in Customer
                                            </strong>
                                        </span>
                                    </div>
                                </template>
                            </div>

                            <!-- Totals -->

                            <div
                                class="space-y-3 border-y border-slate-200 py-5"
                            >
                                <div
                                    class="flex justify-between text-sm"
                                >
                                    <span
                                        class="text-slate-500"
                                    >
                                        Subtotal
                                    </span>

                                    <span
                                        class="font-semibold text-slate-900"
                                    >
                                        {{
                                            formatMoney(
                                                subtotal
                                            )
                                        }}
                                    </span>
                                </div>

                                <!-- Discount -->

                                <div>
                                    <label
                                        class="mb-2 block text-xs font-semibold text-slate-500"
                                    >
                                        Discount
                                    </label>

                                    <input
                                        v-model.number="discount"
                                        type="number"
                                        min="0"
                                        :max="subtotal"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-4 focus:ring-green-500/10"
                                    />
                                </div>

                                <!-- Total -->

                                <div
                                    class="flex items-center justify-between pt-2"
                                >
                                    <span
                                        class="text-lg font-bold text-slate-900"
                                    >
                                        Total
                                    </span>

                                    <span
                                        class="text-2xl font-extrabold text-green-600"
                                    >
                                        {{
                                            formatMoney(total)
                                        }}
                                    </span>
                                </div>
                            </div>

                            <!-- Payment Method -->

                            <div>
                                <label
                                    class="mb-3 block text-sm font-semibold text-slate-900"
                                >
                                    Payment Method
                                </label>

                                <div
                                    class="grid grid-cols-3 gap-2"
                                >
                                    <button
                                        v-for="method in paymentMethods"
                                        :key="method.value"
                                        type="button"
                                        @click="
                                            paymentMethod =
                                                method.value
                                        "
                                        class="rounded-xl border px-3 py-3 text-sm font-semibold transition"
                                        :class="
                                            paymentMethod ===
                                            method.value
                                                ? 'border-green-600 bg-green-600 text-white'
                                                : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                        "
                                    >
                                        {{
                                            method.label
                                        }}
                                    </button>
                                </div>
                            </div>

                            <!-- Complete Sale -->

                            <button
                                type="button"
                                @click="completeSale"
                                :disabled="
                                    cart.length === 0 ||
                                    completingSale
                                "
                                class="w-full rounded-xl bg-green-600 px-5 py-4 text-sm font-bold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{
                                    completingSale
                                        ? 'Completing Sale...'
                                        : `Complete Sale · ${formatMoney(total)}`
                                }}
                            </button>
                        </div>
                    </aside>
                </div>
            </main>
        </div>
    </AdminLayout>
</template>