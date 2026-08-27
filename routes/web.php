<?php

use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpiryReminderController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\PrescriptionController as AdminPrescriptionController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PurchaseController;

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Product;
use App\Support\Settings;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


/*
|--------------------------------------------------------------------------
| Maintenance Middleware
|--------------------------------------------------------------------------
*/

Route::middleware('maintenance')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Website
    |--------------------------------------------------------------------------
    */

    Route::get('/', function (Settings $settings) {

        /*
        |--------------------------------------------------------------------------
        | Advertisements
        |--------------------------------------------------------------------------
        */

        $advertisements = Advertisement::query()
            ->with('product:id,name,slug,image')
            ->active()
            ->orderBy('sort_order')
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'description',
                'image',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Featured Products
        |--------------------------------------------------------------------------
        */

        $featuredProducts = Product::query()
            ->with([
                'category:id,name,slug',
                'inventory:id,product_id,quantity,reserved_quantity,minimum_stock',
            ])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get([
                'id',
                'category_id',
                'name',
                'slug',
                'sku',
                'price',
                'image',
                'requires_prescription',
                'is_active',
                'is_featured',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */

        $generalSettings = $settings->all('general');
        $websiteSettings = $settings->all('website');

        return Inertia::render('Home', [
            'advertisements' => $advertisements,
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'website' => $websiteSettings,
            'general' => $generalSettings,
        ]);
    })->name('home');


    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */

    Route::get('/categories', function () {

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'description',
                'image',
            ]);

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    })->name('categories.index');


    /*
    |--------------------------------------------------------------------------
    | Category Products
    |--------------------------------------------------------------------------
    */

    Route::get('/categories/{category:slug}', function (Category $category) {

        abort_unless($category->is_active, 404);

        $products = Product::query()
            ->with([
                'category:id,name,slug',
                'inventory:id,product_id,quantity,reserved_quantity,minimum_stock',
            ])
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Categories/Show', [
            'category' => $category,
            'products' => $products,
        ]);
    })->name('categories.show');


    /*
    |--------------------------------------------------------------------------
    | Shop
    |--------------------------------------------------------------------------
    */

    Route::get('/shop', [
        ShopController::class,
        'index',
    ])->name('shop.index');

    Route::get('/shop/{product:slug}', [
        ShopController::class,
        'show',
    ])->name('shop.show');


    /*
    |--------------------------------------------------------------------------
    | Cart
    |--------------------------------------------------------------------------
    */

    Route::get('/cart', [
        CartController::class,
        'index',
    ])->name('cart.index');

    Route::post('/cart', [
        CartController::class,
        'store',
    ])->name('cart.store');

    Route::patch('/cart/{product}', [
        CartController::class,
        'update',
    ])->name('cart.update');

    Route::delete('/cart/{product}', [
        CartController::class,
        'destroy',
    ])->name('cart.destroy');

    Route::delete('/cart', [
        CartController::class,
        'clear',
    ])->name('cart.clear');


    /*
    |--------------------------------------------------------------------------
    | Checkout
    |--------------------------------------------------------------------------
    */

    Route::get('/checkout', [
        CheckoutController::class,
        'create',
    ])->name('checkout.create');

    Route::post('/checkout', [
        CheckoutController::class,
        'store',
    ])->name('checkout.store');

    Route::get('/checkout/{order}/payment', [
        CheckoutController::class,
        'payment',
    ])->name('checkout.payment');


    /*
    |--------------------------------------------------------------------------
    | Customer Account
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth', 'verified'])->group(function () {

        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', [
            ProfileController::class,
            'edit',
        ])->name('profile.edit');

        Route::patch('/profile', [
            ProfileController::class,
            'update',
        ])->name('profile.update');

        Route::delete('/profile', [
            ProfileController::class,
            'destroy',
        ])->name('profile.destroy');


        /*
        |--------------------------------------------------------------------------
        | Customer Payments
        |--------------------------------------------------------------------------
        */

        Route::get('/payments/{order}/create', [
            PaymentController::class,
            'create',
        ])->name('payments.create');


        /*
        |--------------------------------------------------------------------------
        | Customer Orders
        |--------------------------------------------------------------------------
        */

        Route::get('/orders', [
            OrderController::class,
            'index',
        ])->name('orders.index');

        Route::get('/orders/{order}', [
            OrderController::class,
            'show',
        ])->name('orders.show');


        /*
        |--------------------------------------------------------------------------
        | Customer Prescriptions
        |--------------------------------------------------------------------------
        */

        Route::get('/prescriptions', [
            PrescriptionController::class,
            'index',
        ])->name('prescriptions.index');

        Route::get('/prescriptions/create', [
            PrescriptionController::class,
            'create',
        ])->name('prescriptions.create');

        Route::post('/prescriptions', [
            PrescriptionController::class,
            'store',
        ])->name('prescriptions.store');

        Route::get('/prescriptions/{prescription}', [
            PrescriptionController::class,
            'show',
        ])->name('prescriptions.show');
    });
});


/*
|--------------------------------------------------------------------------
| Admin Panel
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/', [
            DashboardController::class,
            'index',
        ])->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'categories',
            CategoryController::class
        );


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'products',
            AdminProductController::class
        )->except(['show']);


        /*
        |--------------------------------------------------------------------------
        | Advertisements
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'advertisements',
            AdvertisementController::class
        )->except(['show']);


        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'inventory',
            InventoryController::class
        )->only([
            'index',
            'create',
            'store',
            'show',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'orders',
            AdminOrderController::class
        )->only([
            'index',
            'show',
            'edit',
            'update',
        ]);

        Route::post('/orders/{order}/cancel', [
            AdminOrderController::class,
            'cancel',
        ])->name('orders.cancel');

        Route::post('/orders/{order}/fulfill', [
            AdminOrderController::class,
            'fulfill',
        ])->name('orders.fulfill');



        /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        */

        Route::get('/payments/{order}', [
            AdminPaymentController::class,
            'create',
        ])->name('payments.create');


        /*
        |--------------------------------------------------------------------------
        | Prescriptions
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'prescriptions',
            AdminPrescriptionController::class
        )->only([
            'index',
            'show',
            'update',
        ]);

        Route::post(
            '/prescriptions/{prescription}/items',
            [
                AdminPrescriptionController::class,
                'store',
            ]
        )->name('prescriptions.items.store');

        Route::patch(
            '/prescriptions/{prescription}/items/{item}',
            [
                AdminPrescriptionController::class,
                'update',
            ]
        )->name('prescriptions.items.update');

        Route::delete(
            '/prescriptions/{prescription}/items/{item}',
            [
                AdminPrescriptionController::class,
                'destroy',
            ]
        )->name('prescriptions.items.destroy');

        Route::post(
            '/prescriptions/{prescription}/create-order',
            [
                AdminPrescriptionController::class,
                'createOrder',
            ]
        )->name('prescriptions.create-order');


        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'customers',
            CustomerController::class
        )->only([
            'index',
            'show',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Reviews
        |--------------------------------------------------------------------------
        */

        Route::get('/reviews', [
            AdminReviewController::class,
            'index',
        ])->name('reviews.index');

        Route::get('/reviews/{review}', [
            AdminReviewController::class,
            'show',
        ])->name('reviews.show');

        Route::post('/reviews/{review}/approve', [
            AdminReviewController::class,
            'approve',
        ])->name('reviews.approve');

        Route::post('/reviews/{review}/reject', [
            AdminReviewController::class,
            'reject',
        ])->name('reviews.reject');

        Route::delete('/reviews/{review}', [
            AdminReviewController::class,
            'destroy',
        ])->name('reviews.destroy');


        /*
        |--------------------------------------------------------------------------
        | POS
        |--------------------------------------------------------------------------
        */

        Route::get('/pos', [
            PosController::class,
            'index',
        ])->name('pos.index');

        Route::get('/pos/products', [
            PosController::class,
            'products',
        ])->name('pos.products');

        Route::get('/pos/customers', [
            PosController::class,
            'customers',
        ])->name('pos.customers');

        Route::post('/pos', [
            PosController::class,
            'store',
        ])->name('pos.store');

        Route::get('/pos/history', [
            PosController::class,
            'history',
        ])->name('pos.history');

        Route::get('/pos/{order}/receipt', [
            PosController::class,
            'receipt',
        ])->name('pos.receipt');

        Route::get('/pos/sales/{order}', [
            PosController::class,
            'show',
        ])->name('pos.show');


        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        Route::get('/reports', [
            ReportController::class,
            'index',
        ])->name('reports.index');


        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */

        Route::get('/settings', [
            SettingsController::class,
            'index',
        ])->name('settings.index');

        Route::put('/settings', [
            SettingsController::class,
            'update',
        ])->name('settings.update');


        /*
        |--------------------------------------------------------------------------
        | Suppliers
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'suppliers',
            SupplierController::class
        );

        Route::patch(
            '/suppliers/{supplier}/toggle-status',
            [
                SupplierController::class,
                'toggleStatus',
            ]
        )->name('suppliers.toggleStatus');


        /*
        |--------------------------------------------------------------------------
        | Purchases
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'purchases',
            PurchaseController::class
        );

        Route::post(
            '/purchases/{purchase}/receive',
            [
                PurchaseController::class,
                'receive',
            ]
        )->name('purchases.receive');


        /*
        |--------------------------------------------------------------------------
        | Expiry Reminder
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/expiry-reminder',
            [
                ExpiryReminderController::class,
                'index',
            ]
        )->name('expiry-reminder');

        Route::post(
            '/expiry-reminder/{purchaseItem}/mark-expired',
            [
                ExpiryReminderController::class,
                'markExpired',
            ]
        )->name('expiry-reminder.mark-expired');

        Route::post(
            '/expiry-reminder/{purchaseItem}/return-to-supplier',
            [
                ExpiryReminderController::class,
                'returnToSupplier',
            ]
        )->name('expiry-reminder.return-to-supplier');
    });


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';
