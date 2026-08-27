<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PrescriptionController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\WishlistController;

use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Api\V1\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Api\V1\Admin\PrescriptionController as AdminPrescriptionController;
use App\Http\Controllers\Api\V1\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Api\V1\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\V1\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Api\V1\Admin\ReportController as AdminReportController;


use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | API Health Check
    |--------------------------------------------------------------------------
    */

    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'Go Pharmacy API is running.',
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('/auth/register', [
        AuthController::class,
        'register',
    ]);

    Route::post('/auth/login', [
        AuthController::class,
        'login',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Public Categories
    |--------------------------------------------------------------------------
    */

    Route::get('/categories', [
        CategoryController::class,
        'index',
    ]);

    Route::get('/categories/{category}/products', [
        CategoryController::class,
        'products',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Public Products
    |--------------------------------------------------------------------------
    */

    Route::get('/products', [
        ProductController::class,
        'index',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Product Reviews - Public
    |--------------------------------------------------------------------------
    |
    | Customers can view approved product reviews without authentication.
    |
    */

    Route::get('/products/{product}/reviews', [
        ReviewController::class,
        'index',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Specific Product Routes
    |--------------------------------------------------------------------------
    |
    | Keep these routes after the more specific /reviews route.
    |
    */

    Route::get('/products/{product}/related', [
        ProductController::class,
        'related',
    ]);

    Route::get('/products/{product}', [
        ProductController::class,
        'show',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Guest Order Lookup
    |--------------------------------------------------------------------------
    |
    | Guests can retrieve an order using the order lookup endpoint.
    |
    */

    Route::get('/orders/lookup', [
        OrderController::class,
        'lookup',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Guest Payment Placeholder
    |--------------------------------------------------------------------------
    |
    | Payment gateway integration is intentionally inactive.
    |
    */

    Route::post('/orders/{order}/payment', [
        OrderController::class,
        'createPayment',
    ]);

    Route::get('/orders/{order}/payments', [
        OrderController::class,
        'payments',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Session-Based Cart & Checkout
    |--------------------------------------------------------------------------
    |
    | The current Go Pharmacy cart is session-backed.
    |
    */

    Route::middleware(StartSession::class)->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Cart
        |--------------------------------------------------------------------------
        */

        Route::get('/cart', [
            CartController::class,
            'index',
        ]);

        Route::post('/cart/items', [
            CartController::class,
            'store',
        ]);

        Route::patch('/cart/items/{product}', [
            CartController::class,
            'update',
        ]);

        Route::delete('/cart/items/{product}', [
            CartController::class,
            'destroy',
        ]);

        Route::delete('/cart', [
            CartController::class,
            'clear',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Checkout
        |--------------------------------------------------------------------------
        */

        Route::post('/checkout', [
            CheckoutController::class,
            'store',
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | Authenticated Customer API
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        Route::get('/auth/me', [
            AuthController::class,
            'me',
        ]);

        Route::post('/auth/logout', [
            AuthController::class,
            'logout',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', [
            ProfileController::class,
            'show',
        ]);

        Route::patch('/profile', [
            ProfileController::class,
            'update',
        ]);

        Route::patch('/profile/password', [
            ProfileController::class,
            'updatePassword',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Customer Orders
        |--------------------------------------------------------------------------
        */

        Route::get('/orders', [
            OrderController::class,
            'index',
        ]);

        Route::get('/orders/{order}', [
            OrderController::class,
            'show',
        ]);

        Route::post('/orders/{order}/cancel', [
            OrderController::class,
            'cancel',
        ])->name('orders.cancel');


        /*
        |--------------------------------------------------------------------------
        | Customer Addresses
        |--------------------------------------------------------------------------
        */

        Route::get('/addresses', [
            AddressController::class,
            'index',
        ]);

        Route::post('/addresses', [
            AddressController::class,
            'store',
        ]);

        Route::get('/addresses/{address}', [
            AddressController::class,
            'show',
        ]);

        Route::patch('/addresses/{address}', [
            AddressController::class,
            'update',
        ]);

        Route::delete('/addresses/{address}', [
            AddressController::class,
            'destroy',
        ]);

        Route::patch('/addresses/{address}/default', [
            AddressController::class,
            'setDefault',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Wishlist
        |--------------------------------------------------------------------------
        */

        Route::get('/wishlist', [
            WishlistController::class,
            'index',
        ]);

        Route::post('/wishlist/{product}', [
            WishlistController::class,
            'store',
        ]);

        Route::delete('/wishlist/{product}', [
            WishlistController::class,
            'destroy',
        ]);

        Route::delete('/wishlist', [
            WishlistController::class,
            'clear',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Prescriptions
        |--------------------------------------------------------------------------
        */

        Route::get('/prescriptions', [
            PrescriptionController::class,
            'index',
        ]);

        Route::post('/prescriptions', [
            PrescriptionController::class,
            'store',
        ]);

        Route::get('/prescriptions/{prescription}', [
            PrescriptionController::class,
            'show',
        ]);

        Route::patch('/prescriptions/{prescription}', [
            PrescriptionController::class,
            'update',
        ]);

        Route::delete('/prescriptions/{prescription}', [
            PrescriptionController::class,
            'destroy',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Product Reviews - Authenticated Actions
        |--------------------------------------------------------------------------
        |
        | Viewing reviews is public.
        | Creating, updating and deleting reviews requires authentication.
        |
        */

        Route::post('/products/{product}/reviews', [
            ReviewController::class,
            'store',
        ]);

        Route::get('/products/{product}/reviews/{review}', [
            ReviewController::class,
            'show',
        ]);

        Route::patch('/products/{product}/reviews/{review}', [
            ReviewController::class,
            'update',
        ]);

        Route::delete('/products/{product}/reviews/{review}', [
            ReviewController::class,
            'destroy',
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | Admin API
    |--------------------------------------------------------------------------
    |
    | All admin API endpoints require:
    |
    | 1. Sanctum authentication
    | 2. Admin authorization
    |
    */

    Route::middleware(['auth:sanctum', 'admin'])
        ->prefix('admin')
        ->name('api.admin.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            Route::get('/dashboard', [
                AdminDashboardController::class,
                'index',
            ])->name('dashboard');


            /*
            |--------------------------------------------------------------------------
            | Products
            |--------------------------------------------------------------------------
            */

            Route::get('/products', [
                AdminProductController::class,
                'index',
            ])->name('products.index');

            Route::post('/products', [
                AdminProductController::class,
                'store',
            ])->name('products.store');

            Route::get('/products/{product}', [
                AdminProductController::class,
                'show',
            ])->name('products.show');

            Route::patch('/products/{product}', [
                AdminProductController::class,
                'update',
            ])->name('products.update');

            Route::delete('/products/{product}', [
                AdminProductController::class,
                'destroy',
            ])->name('products.destroy');

        
            /*
            |--------------------------------------------------------------------------
            | Categories
            |--------------------------------------------------------------------------
            */

            Route::get('/categories', [
                AdminCategoryController::class,
                'index',
            ])->name('categories.index');

            Route::post('/categories', [
                AdminCategoryController::class,
                'store',
            ])->name('categories.store');

            Route::get('/categories/{category}', [
                AdminCategoryController::class,
                'show',
            ])->name('categories.show');

            Route::patch('/categories/{category}', [
                AdminCategoryController::class,
                'update',
            ])->name('categories.update');

            Route::delete('/categories/{category}', [
                AdminCategoryController::class,
                'destroy',
            ])->name('categories.destroy');

            /*
            |--------------------------------------------------------------------------
            | Orders
            |--------------------------------------------------------------------------
            */

            Route::get('/orders', [
                AdminOrderController::class,
                'index',
            ])->name('orders.index');

            Route::get('/orders/{order}', [
                AdminOrderController::class,
                'show',
            ])->name('orders.show');

            Route::patch('/orders/{order}', [
                AdminOrderController::class,
                'update',
            ])->name('orders.update');

            /*
            |--------------------------------------------------------------------------
            | Payments
            |--------------------------------------------------------------------------
            */

            Route::get('/payments', [
                AdminPaymentController::class,
                'index',
            ])->name('payments.index');

            Route::get('/payments/{payment}', [
                AdminPaymentController::class,
                'show',
            ])->name('payments.show');

            Route::post('/payments/{payment}/successful', [
                AdminPaymentController::class,
                'markAsSuccessful',
            ])->name('payments.successful');

            Route::post('/payments/{payment}/failed', [
                AdminPaymentController::class,
                'markAsFailed',
            ])->name('payments.failed');

        
            /*
            |--------------------------------------------------------------------------
            | Customers
            |--------------------------------------------------------------------------
            */

            Route::get('/customers', [
                AdminCustomerController::class,
                'index',
            ])->name('customers.index');

            Route::get('/customers/{customer}', [
                AdminCustomerController::class,
                'show',
            ])->name('customers.show');

            /*
            |--------------------------------------------------------------------------
            | Prescriptions
            |--------------------------------------------------------------------------
            */

            Route::get('/prescriptions', [
                AdminPrescriptionController::class,
                'index',
            ])->name('prescriptions.index');

            Route::get('/prescriptions/{prescription}', [
                AdminPrescriptionController::class,
                'show',
            ])->name('prescriptions.show');

            Route::patch('/prescriptions/{prescription}', [
                AdminPrescriptionController::class,
                'update',
            ])->name('prescriptions.update');

            Route::post('/prescriptions/{prescription}/order', [
                AdminPrescriptionController::class,
                'createOrder',
            ])->name('prescriptions.order');

            
            /*
            |--------------------------------------------------------------------------
            | Inventory
            |--------------------------------------------------------------------------
            */

            Route::get('/inventory', [
                AdminInventoryController::class,
                'index',
            ])->name('inventory.index');

            Route::post('/inventory', [
                AdminInventoryController::class,
                'store',
            ])->name('inventory.store');

            Route::get('/inventory/{inventory}', [
                AdminInventoryController::class,
                'show',
            ])->name('inventory.show');

            
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
            | Settings
            |--------------------------------------------------------------------------
            */

            Route::get('/settings', [
                AdminSettingsController::class,
                'index',
            ])->name('settings.index');

            Route::patch('/settings', [
                AdminSettingsController::class,
                'update',
            ])->name('settings.update');


        
            /*
            |--------------------------------------------------------------------------
            | Reports
            |--------------------------------------------------------------------------
            */

            Route::get('/reports', [
                AdminReportController::class,
                'index',
            ])->name('reports.index');







        });
});
