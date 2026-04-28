<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/product/{slug}', [HomeController::class, 'show'])->name('product.show');
Route::post('/checkout', [\App\Http\Controllers\OrderController::class, 'storeWeb'])->name('checkout.web');

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/template', [OrderController::class, 'downloadTemplate'])->name('orders.template');
    Route::post('/orders/bulk-upload', [OrderController::class, 'bulkUpload'])->name('orders.bulkUpload');
    Route::post('/orders/bulk-manual', [OrderController::class, 'bulkManualStore'])->name('orders.bulkManualStore');
    Route::post('/orders/bulk-print', [OrderController::class, 'bulkPrint'])->name('orders.bulkPrint');
    Route::post('/orders/bulk-delete', [OrderController::class, 'bulkDelete'])->name('orders.bulkDelete');
    Route::post('/orders/bulk-ship', [OrderController::class, 'bulkShip'])->name('orders.bulkShip');
    Route::post('/orders/bulk-status-update', [OrderController::class, 'bulkStatusUpdate'])->name('orders.bulkStatusUpdate');
    Route::post('orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{order}/ship', [OrderController::class, 'shipWithPathao'])->name('orders.ship');
    Route::post('/orders/{order}/sync-pathao', [OrderController::class, 'syncPathaoStatus'])->name('orders.syncPathaoStatus');
    Route::post('/orders/master-sync-pathao', [OrderController::class, 'masterSyncPathao'])->name('orders.masterSyncPathao');
    Route::get('orders/{order}/print-label', [OrderController::class, 'printLabel'])->name('orders.printLabel');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/verify-return', [OrderController::class, 'verifyReturn'])->name('orders.verifyReturn');
    Route::patch('/orders/{order}/amount', [OrderController::class, 'updateAmount'])->name('orders.updateAmount');
    Route::put('/orders/{order}/full-update', [OrderController::class, 'fullUpdate'])->name('orders.fullUpdate');
    Route::post('/orders/{order}/payment', [OrderController::class, 'recordPayment'])->name('orders.payment');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/{order}/pathao-details', [OrderController::class, 'getPathaoDetails'])->name('orders.pathaoDetails');

    Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');

    // Categories
    Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');

    // Accounting / Inventory
    Route::get('/accounting', [\App\Http\Controllers\AccountingController::class, 'index'])->name('accounting.index');
    Route::post('/accounting/sync-pathao', [\App\Http\Controllers\AccountingController::class, 'syncPathao'])->name('accounting.syncPathao');
    Route::post('/accounting/pay-purchase', [\App\Http\Controllers\AccountingController::class, 'payPurchase'])->name('accounting.payPurchase');
    Route::post('/accounting/store-category', [\App\Http\Controllers\AccountingController::class, 'storeCategory'])->name('accounting.storeCategory');
    Route::post('/accounting/store-party', [\App\Http\Controllers\AccountingController::class, 'storeParty'])->name('accounting.storeParty');
    Route::post('/accounting/store-transaction', [\App\Http\Controllers\AccountingController::class, 'storeTransaction'])->name('accounting.storeTransaction');
    Route::post('/accounting/adjust-stock', [\App\Http\Controllers\AccountingController::class, 'adjustStock'])->name('accounting.adjustStock');
    Route::post('/accounting/sale-return', [\App\Http\Controllers\AccountingController::class, 'saleReturn'])->name('accounting.saleReturn');
    Route::get('/accounting/find-order', [\App\Http\Controllers\AccountingController::class, 'findOrder'])->name('accounting.findOrder');
    Route::get('/accounting/export-report', [\App\Http\Controllers\AccountingController::class, 'exportReport'])->name('accounting.exportReport');
    Route::post('/orders/pos', [OrderController::class, 'storePOS'])->name('orders.pos');

    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class)->except(['create', 'show', 'edit']);
    Route::resource('purchases', \App\Http\Controllers\PurchaseController::class)->except(['create', 'show', 'edit', 'update']);
    Route::patch('/purchases/{purchase}/amount', [\App\Http\Controllers\PurchaseController::class, 'updateAmount'])->name('purchases.updateAmount');

    // Admin Only Routes
    Route::middleware(['admin'])->group(function () {
        // Settings
        Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'store'])->name('settings.store');
        Route::post('/settings/test-pathao', [\App\Http\Controllers\SettingController::class, 'testPathao'])->name('settings.testPathao');
        Route::post('/settings/factory-reset', [\App\Http\Controllers\SettingController::class, 'factoryReset'])->name('settings.factoryReset');

        // Staff / Users
        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['create', 'show', 'edit']);
    });

    // Customers CRM
    Route::get('/customers', [\App\Http\Controllers\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{phone}', [\App\Http\Controllers\CustomerController::class, 'show'])->name('customers.show');


    // Pathao Manager Routes
    Route::get('/pathao', [\App\Http\Controllers\PathaoManagerController::class, 'index'])->name('pathao.index');
    Route::post('/pathao/settlement', [\App\Http\Controllers\PathaoManagerController::class, 'recordSettlement'])->name('pathao.settlement');
    Route::get('/api/pathao/cities', [\App\Http\Controllers\PathaoManagerController::class, 'getCities']);
    Route::get('/api/pathao/zones/{cityId}', [\App\Http\Controllers\PathaoManagerController::class, 'getZones']);
    Route::get('/api/pathao/areas/{zoneId}', [\App\Http\Controllers\PathaoManagerController::class, 'getAreas']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
