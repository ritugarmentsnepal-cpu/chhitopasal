<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [HomeController::class, 'shop'])->name('shop');
Route::get('/flash-sales', [HomeController::class, 'flashSales'])->name('frontend.flash-sales');
Route::get('/company-profile', [HomeController::class, 'companyProfile'])->name('company.profile');
Route::get('/privacy-policy', [HomeController::class, 'privacyPolicy'])->name('privacy.policy');
Route::get('/product/{slug}', [HomeController::class, 'show'])->name('product.show');
Route::post('/checkout', [\App\Http\Controllers\OrderController::class, 'storeWeb'])->middleware('throttle:5,1')->name('checkout.web');

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    // Orders — permission:orders
    Route::middleware(['permission:orders'])->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/template', [OrderController::class, 'downloadTemplate'])->middleware('throttle:5,1')->name('orders.template');
        Route::post('/orders/bulk-upload', [OrderController::class, 'bulkUpload'])->name('orders.bulkUpload');
        Route::post('/orders/bulk-manual', [OrderController::class, 'bulkManualStore'])->name('orders.bulkManualStore');
        Route::get('/orders/bulk-batches', [OrderController::class, 'bulkBatches'])->name('orders.bulkBatches');
        Route::get('/orders/bulk-batches/{batchId}', [OrderController::class, 'bulkBatchShow'])->name('orders.bulkBatchShow');
        Route::post('/orders/bulk-print', [OrderController::class, 'bulkPrint'])->name('orders.bulkPrint');
        Route::post('/orders/bulk-delete', [OrderController::class, 'bulkDelete'])->name('orders.bulkDelete');
        Route::post('/orders/bulk-ship', [OrderController::class, 'bulkShip'])->name('orders.bulkShip');
        Route::get('/orders/bulk-shipments', [OrderController::class, 'bulkShipments'])->name('orders.bulkShipments');
        Route::get('/orders/bulk-shipments/{batchId}/print', [OrderController::class, 'bulkShipmentPrint'])->name('orders.bulkShipmentPrint');
        Route::post('/orders/bulk-status-update', [OrderController::class, 'bulkStatusUpdate'])->name('orders.bulkStatusUpdate');

        Route::post('/orders/{order}/ship', [OrderController::class, 'shipWithPathao'])->name('orders.ship');
        Route::post('/orders/{order}/sync-pathao', [OrderController::class, 'syncPathaoStatus'])->middleware('throttle:10,1')->name('orders.syncPathaoStatus');
        Route::post('/orders/master-sync-pathao', [OrderController::class, 'masterSyncPathao'])->name('orders.masterSyncPathao');
        Route::get('orders/{order}/print-label', [OrderController::class, 'printLabel'])->name('orders.printLabel');
        Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('/orders/{order}/verify-return', [OrderController::class, 'verifyReturn'])->name('orders.verifyReturn');
        Route::patch('/orders/{order}/amount', [OrderController::class, 'updateAmount'])->name('orders.updateAmount');
        Route::put('/orders/{order}/full-update', [OrderController::class, 'fullUpdate'])->name('orders.fullUpdate');
        Route::post('/orders/{order}/payment', [OrderController::class, 'recordPayment'])->name('orders.payment');
        Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('/orders/{order}/pathao-details', [OrderController::class, 'getPathaoDetails'])->middleware('throttle:10,1')->name('orders.pathaoDetails');
        Route::get('/orders/reports/damage', [OrderController::class, 'damageReport'])->name('orders.damageReport');
    });

    // POS — permission:pos
    Route::post('/orders/pos', [OrderController::class, 'storePOS'])->middleware('permission:pos')->name('orders.pos');

    // Products — permission:products
    Route::middleware(['permission:products'])->group(function () {
        Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
        Route::post('/products/ai-generate', [\App\Http\Controllers\ProductAIController::class, 'generate'])->name('products.ai-generate');
        Route::post('/products/ai-generate-thumbnails', [\App\Http\Controllers\ProductAIController::class, 'generateThumbnails'])->name('products.ai-generate-thumbnails');
        Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');
        
        // Flash Sales
        Route::get('/admin/flash-sales', [\App\Http\Controllers\AdminFlashSaleController::class, 'index'])->name('flash-sales.index');
        Route::post('/admin/flash-sales/{product}', [\App\Http\Controllers\AdminFlashSaleController::class, 'update'])->name('flash-sales.update');
    });

    // Categories — permission:categories
    Route::middleware(['permission:categories'])->group(function () {
        Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // Accounting — permission:accounting
    Route::middleware(['permission:accounting'])->group(function () {
        Route::get('/accounting', [\App\Http\Controllers\AccountingController::class, 'index'])->name('accounting.index');
        Route::post('/accounting/sync-pathao', [\App\Http\Controllers\AccountingController::class, 'syncPathao'])->middleware('throttle:5,1')->name('accounting.syncPathao');
        Route::post('/accounting/pay-purchase', [\App\Http\Controllers\AccountingController::class, 'payPurchase'])->name('accounting.payPurchase');
        Route::post('/accounting/store-category', [\App\Http\Controllers\AccountingController::class, 'storeCategory'])->name('accounting.storeCategory');
        Route::post('/accounting/store-party', [\App\Http\Controllers\AccountingController::class, 'storeParty'])->name('accounting.storeParty');
        Route::post('/accounting/update-party/{party}', [\App\Http\Controllers\AccountingController::class, 'updateParty'])->name('accounting.updateParty');
        Route::post('/accounting/store-transaction', [\App\Http\Controllers\AccountingController::class, 'storeTransaction'])->name('accounting.storeTransaction');
        Route::post('/accounting/adjust-stock', [\App\Http\Controllers\AccountingController::class, 'adjustStock'])->name('accounting.adjustStock');
        Route::post('/accounting/sale-return', [\App\Http\Controllers\AccountingController::class, 'saleReturn'])->name('accounting.saleReturn');
        Route::get('/accounting/find-order', [\App\Http\Controllers\AccountingController::class, 'findOrder'])->name('accounting.findOrder');
        Route::get('/accounting/export-report', [\App\Http\Controllers\AccountingController::class, 'exportReport'])->middleware('throttle:5,1')->name('accounting.exportReport');
        Route::post('/accounting/reconcile', [\App\Http\Controllers\AccountingController::class, 'reconcile'])->name('accounting.reconcile');

        // Account Management
        Route::post('/accounting/accounts', [\App\Http\Controllers\AccountingController::class, 'storeAccount'])->name('accounting.storeAccount');
        Route::put('/accounting/accounts/{account}', [\App\Http\Controllers\AccountingController::class, 'updateAccount'])->name('accounting.updateAccount');
        Route::delete('/accounting/accounts/{account}', [\App\Http\Controllers\AccountingController::class, 'destroyAccount'])->name('accounting.destroyAccount');
        Route::post('/accounting/transfer', [\App\Http\Controllers\AccountingController::class, 'transferFunds'])->name('accounting.transferFunds');
        Route::get('/accounting/statement/{account}', [\App\Http\Controllers\AccountingController::class, 'accountStatement'])->name('accounting.statement');
        Route::get('/accounting/statement/{account}/export', [\App\Http\Controllers\AccountingController::class, 'exportStatement'])->middleware('throttle:5,1')->name('accounting.exportStatement');

        // HR & Payroll
        Route::post('/accounting/employees', [\App\Http\Controllers\AccountingController::class, 'storeEmployee'])->name('accounting.storeEmployee');
        Route::post('/accounting/attendance', [\App\Http\Controllers\AccountingController::class, 'storeAttendance'])->name('accounting.storeAttendance');
        Route::post('/accounting/advances', [\App\Http\Controllers\AccountingController::class, 'storeAdvance'])->name('accounting.storeAdvance');
        Route::post('/accounting/payroll/generate', [\App\Http\Controllers\AccountingController::class, 'generatePayroll'])->name('accounting.generatePayroll');
        Route::post('/accounting/payroll/pay', [\App\Http\Controllers\AccountingController::class, 'payPayroll'])->name('accounting.payPayroll');
    });

    // Expenses — permission:expenses
    Route::middleware(['permission:expenses'])->group(function () {
        Route::resource('expenses', \App\Http\Controllers\ExpenseController::class)->except(['create', 'show', 'edit']);
    });

    // Purchases — permission:purchases
    Route::middleware(['permission:purchases'])->group(function () {
        Route::resource('purchases', \App\Http\Controllers\PurchaseController::class)->except(['create', 'show', 'edit', 'update']);
        Route::patch('/purchases/{purchase}/amount', [\App\Http\Controllers\PurchaseController::class, 'updateAmount'])->name('purchases.updateAmount');
    });

    // Admin Only Routes
    Route::middleware(['admin'])->group(function () {
        // Settings
        Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'store'])->name('settings.store');
        Route::post('/settings/test-pathao', [\App\Http\Controllers\SettingController::class, 'testPathao'])->name('settings.testPathao');
        Route::post('/settings/factory-reset', [\App\Http\Controllers\SettingController::class, 'factoryReset'])->name('settings.factoryReset');
        
        // Staff / Users
        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['create', 'show', 'edit']);
        Route::patch('/users/{user}/permissions', [\App\Http\Controllers\UserController::class, 'updatePermissions'])->name('users.updatePermissions');

        // Analytics
        Route::get('/admin/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');

        // Activity Log
        Route::get('/admin/activity-log', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-log.index');

        // Admin Utilities — SEC-09: Changed from GET to POST to prevent CSRF via link/image tags
        Route::post('/admin/clear-cache', function () {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            clear_settings_cache();
            return back()->with('success', 'All caches cleared successfully!');
        })->name('admin.clearCache');

        Route::post('/admin/optimize', function () {
            \Illuminate\Support\Facades\Artisan::call('config:cache');
            \Illuminate\Support\Facades\Artisan::call('route:cache');
            \Illuminate\Support\Facades\Artisan::call('view:cache');
            return back()->with('success', 'Application optimized for production!');
        })->name('admin.optimize');
    });

    // Customers CRM — permission:customers
    Route::middleware(['permission:customers'])->group(function () {
        Route::get('/customers', [\App\Http\Controllers\CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{phone}', [\App\Http\Controllers\CustomerController::class, 'show'])->name('customers.show');
    });

    // Pathao Manager — permission:pathao
    Route::middleware(['permission:pathao'])->group(function () {
        Route::get('/pathao', [\App\Http\Controllers\PathaoManagerController::class, 'index'])->name('pathao.index');
        Route::post('/pathao/settlement', [\App\Http\Controllers\PathaoManagerController::class, 'recordSettlement'])->name('pathao.settlement');
        
        // Reconciliation
        Route::get('/pathao/reconcile', [\App\Http\Controllers\PathaoReconciliationController::class, 'index'])->name('pathao.reconcile.index');
        Route::post('/pathao/reconcile/preview', [\App\Http\Controllers\PathaoReconciliationController::class, 'preview'])->name('pathao.reconcile.preview');
        Route::post('/pathao/reconcile/process', [\App\Http\Controllers\PathaoReconciliationController::class, 'process'])->name('pathao.reconcile.process');
    });

    // Rider Comments Inbox - accessible to all authenticated users
    Route::get('/rider-comments', [\App\Http\Controllers\RiderCommentController::class, 'index'])->name('rider_comments.index');
    Route::post('/rider-comments/{comment}/reply', [\App\Http\Controllers\RiderCommentController::class, 'reply'])->name('rider_comments.reply');
    Route::post('/rider-comments/{comment}/assign', [\App\Http\Controllers\RiderCommentController::class, 'assign'])->name('rider_comments.assign');
    Route::post('/rider-comments/{comment}/tag', [\App\Http\Controllers\RiderCommentController::class, 'tag'])->name('rider_comments.tag');
    Route::post('/rider-comments/{comment}/resolve', [\App\Http\Controllers\RiderCommentController::class, 'markResolved'])->name('rider_comments.resolve');
    Route::get('/api/rider-comments/unread-count', [\App\Http\Controllers\RiderCommentController::class, 'unreadCount'])->name('api.rider_comments.unreadCount');

    // Pathao API proxies (needed by orders too, so just auth-gated)
    // ARCH-05: Add rate limiting to API proxy routes
    Route::get('/api/pathao/cities', [\App\Http\Controllers\PathaoManagerController::class, 'getCities'])->middleware('throttle:30,1');
    Route::get('/api/pathao/zones/{cityId}', [\App\Http\Controllers\PathaoManagerController::class, 'getZones'])->middleware('throttle:30,1');
    Route::get('/api/pathao/areas/{zoneId}', [\App\Http\Controllers\PathaoManagerController::class, 'getAreas'])->middleware('throttle:30,1');

    // Facebook Inbox — permission:facebook_inbox
    Route::middleware(['permission:facebook_inbox'])->group(function () {
        Route::get('/facebook-inbox', [\App\Http\Controllers\FacebookInboxController::class, 'index'])->name('facebook-inbox.index');
        Route::get('/facebook/login', [\App\Http\Controllers\FacebookInboxController::class, 'login'])->name('facebook.login');
        Route::get('/facebook/callback', [\App\Http\Controllers\FacebookInboxController::class, 'callback'])->name('facebook.callback');
        
        Route::get('/api/facebook/pages/{pageId}/conversations', [\App\Http\Controllers\Api\FacebookApiController::class, 'conversations'])->name('api.facebook.conversations');
        Route::get('/api/facebook/pages/{pageId}/conversations/{threadId}/messages', [\App\Http\Controllers\Api\FacebookApiController::class, 'messages'])->name('api.facebook.messages');
        Route::post('/api/facebook/pages/{pageId}/conversations/{threadId}/messages', [\App\Http\Controllers\Api\FacebookApiController::class, 'sendMessage'])->name('api.facebook.sendMessage');
        Route::post('/api/facebook/pages/{pageId}/conversations/{threadId}/mark-read', [\App\Http\Controllers\Api\FacebookApiController::class, 'markAsRead'])->name('api.facebook.markRead');
        
        Route::get('/api/facebook/pages/{pageId}/posts', [\App\Http\Controllers\Api\FacebookApiController::class, 'posts'])->name('api.facebook.posts');
        Route::get('/api/facebook/pages/{pageId}/posts/{postId}/comments', [\App\Http\Controllers\Api\FacebookApiController::class, 'postComments'])->name('api.facebook.postComments');
        Route::post('/api/facebook/pages/{pageId}/comments/{commentId}/reply', [\App\Http\Controllers\Api\FacebookApiController::class, 'replyToComment'])->name('api.facebook.replyToComment');
        Route::post('/api/facebook/pages/{pageId}/comments/{commentId}/hide', [\App\Http\Controllers\Api\FacebookApiController::class, 'hideComment'])->name('api.facebook.hideComment');
        Route::delete('/api/facebook/pages/{pageId}/comments/{commentId}', [\App\Http\Controllers\Api\FacebookApiController::class, 'deleteComment'])->name('api.facebook.deleteComment');
        Route::post('/api/facebook/pages/{pageId}/comments/{commentId}/like', [\App\Http\Controllers\Api\FacebookApiController::class, 'likeComment'])->name('api.facebook.likeComment');

        Route::get('/api/facebook/saved-replies', [\App\Http\Controllers\Api\FacebookApiController::class, 'getSavedReplies'])->name('api.facebook.savedReplies.index');
        Route::post('/api/facebook/saved-replies', [\App\Http\Controllers\Api\FacebookApiController::class, 'storeSavedReply'])->name('api.facebook.savedReplies.store');
        Route::delete('/api/facebook/saved-replies/{id}', [\App\Http\Controllers\Api\FacebookApiController::class, 'deleteSavedReply'])->name('api.facebook.savedReplies.destroy');

        // AI Agent Training Page
        Route::get('/ai-agent', [\App\Http\Controllers\AiAgentController::class, 'index'])->name('ai-agent.index');
        Route::post('/ai-agent/knowledge', [\App\Http\Controllers\AiAgentController::class, 'storeKnowledge'])->name('ai-agent.storeKnowledge');
        Route::delete('/ai-agent/knowledge/{id}', [\App\Http\Controllers\AiAgentController::class, 'deleteKnowledge'])->name('ai-agent.deleteKnowledge');
        Route::post('/ai-agent/knowledge/{id}/toggle', [\App\Http\Controllers\AiAgentController::class, 'toggleKnowledge'])->name('ai-agent.toggleKnowledge');
        Route::post('/ai-agent/sync', [\App\Http\Controllers\AiAgentController::class, 'syncConversations'])->name('ai-agent.sync');
        Route::post('/ai-agent/start-daemon', [\App\Http\Controllers\AiAgentController::class, 'startDaemon'])->name('ai-agent.startDaemon');
        Route::get('/api/ai-agent/stats', [\App\Http\Controllers\AiAgentController::class, 'getTrainingStats'])->name('api.ai-agent.stats');
        Route::post('/api/ai-agent/test', [\App\Http\Controllers\AiAgentController::class, 'testAgent'])->name('api.ai-agent.test');

        // AI Thread Controls (used from FB inbox)
        Route::post('/api/facebook/threads/{threadId}/ai-takeover', [\App\Http\Controllers\AiAgentController::class, 'takeover'])->name('api.ai-agent.takeover');
        Route::post('/api/facebook/threads/{threadId}/ai-resume', [\App\Http\Controllers\AiAgentController::class, 'resume'])->name('api.ai-agent.resume');
        Route::get('/api/facebook/threads/{threadId}/ai-status', [\App\Http\Controllers\AiAgentController::class, 'status'])->name('api.ai-agent.status');

        // Support Tickets
        Route::get('/support-tickets', [\App\Http\Controllers\SupportTicketController::class, 'index'])->name('support-tickets.index');
        Route::post('/support-tickets/{ticket}/update', [\App\Http\Controllers\SupportTicketController::class, 'update'])->name('support-tickets.update');
        Route::post('/support-tickets/{ticket}/resolve', [\App\Http\Controllers\SupportTicketController::class, 'resolve'])->name('support-tickets.resolve');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';

// Storefront Analytics Tracking
Route::post('/track-event', [\App\Http\Controllers\TrackEventController::class, 'store'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->name('track.event');

// Facebook Webhooks
Route::get('/webhook/facebook', [\App\Http\Controllers\Api\FacebookWebhookController::class, 'verify']);
Route::post('/webhook/facebook', [\App\Http\Controllers\Api\FacebookWebhookController::class, 'handle'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Pathao Webhook
Route::post('/webhook/pathao', [\App\Http\Controllers\Api\PathaoWebhookController::class, 'handle'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Live Server Debug Route
Route::get('/debug-webhook', [\App\Http\Controllers\Api\FacebookWebhookController::class, 'debugLiveServer']);


Route::get('/force-subscribe', [\App\Http\Controllers\Api\FacebookWebhookController::class, 'forceSubscribe']);

