<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\ProductController;
use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Master\BrandController;
use App\Http\Controllers\Master\WarehouseController;
use App\Http\Controllers\Purchasing\PurchaseOrderController;
use App\Http\Controllers\Purchasing\ReceivingController;
use App\Http\Controllers\Inventory\StockController;
use App\Http\Controllers\Inventory\MutationController;
use App\Http\Controllers\Inventory\OpnameController;
use App\Http\Controllers\Pos\CashierController;
use App\Http\Controllers\Pos\HistoryController;
use App\Http\Controllers\Pos\ShiftController;
use App\Http\Controllers\Pos\CashRegisterController;
use App\Http\Controllers\Pos\SaleReturnController;
use App\Http\Controllers\Merchandise\PromotionController;
use App\Http\Controllers\Merchandise\MemberController;
use App\Http\Controllers\Merchandise\GiftCardController;
use App\Http\Controllers\Report\SalesTargetController;
use App\Http\Controllers\Purchasing\PurchaseRequestController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\ExpenseCategoryController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Setting\CompanyController;
use App\Http\Controllers\Setting\UserController;
use App\Http\Controllers\Setting\ActivityLogController;
use App\Http\Controllers\Merchandise\MemberCreditController;
use App\Http\Controllers\ProfileController;

// Auth
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {

    // Profile
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::delete('profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');
    Route::post('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data
    Route::prefix('master')->name('master.')->middleware('permission:master.manage')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show', 'edit', 'update']);
        Route::resource('products', ProductController::class);
        Route::get('products/{product}/barcode', [ProductController::class, 'printBarcode'])->name('products.barcode');
        Route::post('products/barcode/multiple', [ProductController::class, 'printBarcodeMultiple'])->name('products.barcode.multiple');
        Route::resource('suppliers', SupplierController::class)->except(['show', 'edit', 'update']);
        Route::resource('units', UnitController::class)->except(['show', 'edit', 'update', 'create']);
        Route::resource('brands', BrandController::class)->except(['show', 'edit', 'update', 'create']);
        Route::resource('warehouses', WarehouseController::class)->except(['show', 'edit', 'update', 'create']);
    });

    // Purchasing
    Route::prefix('purchasing')->name('purchasing.')->middleware('permission:purchasing.manage')->group(function () {
        Route::resource('orders', PurchaseOrderController::class)->except(['edit', 'update']);
        Route::post('orders/{order}/status', [PurchaseOrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::resource('receivings', ReceivingController::class)->except(['edit', 'update', 'destroy']);
        Route::get('receivings/create/{po}', [ReceivingController::class, 'createFromPo'])->name('receivings.create_from_po');
        Route::resource('returns', \App\Http\Controllers\Purchasing\PurchaseReturnController::class)->except(['edit', 'update', 'destroy']);
        Route::get('returns/create/{po}', [\App\Http\Controllers\Purchasing\PurchaseReturnController::class, 'createFromPo'])->name('returns.create_from_po');
        Route::resource('requests', PurchaseRequestController::class)->except(['edit', 'update']);
        Route::post('requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])->name('requests.approve');
        Route::post('requests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject'])->name('requests.reject');
    });

    // Inventory
    Route::prefix('inventory')->name('inventory.')->middleware('permission:stock.manage')->group(function () {
        Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::post('stocks/adjust', [StockController::class, 'adjust'])->name('stocks.adjust');
        Route::get('stocks/card/{product}', [StockController::class, 'card'])->name('stocks.card');
        Route::resource('mutations', MutationController::class)->except(['edit', 'update', 'destroy', 'show']);
        Route::resource('opname', OpnameController::class)->except(['edit', 'update', 'destroy', 'show']);
        Route::get('opname/get-stock', [OpnameController::class, 'getStock'])->name('opname.get-stock');
        Route::get('opname/{opname}', [OpnameController::class, 'show'])->name('opname.show');
        Route::post('opname/{opname}/approve', [OpnameController::class, 'approve'])->name('opname.approve')->middleware('permission:stock.approve');
    });

    // POS
    Route::prefix('pos')->name('pos.')->middleware('permission:pos.access')->group(function () {
        Route::get('cashier', [CashierController::class, 'index'])->name('cashier.index');
        Route::post('cashier/search-product', [CashierController::class, 'searchProduct'])->name('cashier.searchProduct');
        Route::post('cashier/process-sale', [CashierController::class, 'processSale'])->name('cashier.processSale');
        Route::post('cashier/validate-gift-card', [CashierController::class, 'validateGiftCard'])->name('cashier.validateGiftCard');
        Route::post('cashier/get-members', [CashierController::class, 'getMembers'])->name('cashier.getMembers');
        Route::get('history', [HistoryController::class, 'index'])->name('history.index');
        Route::get('shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::post('shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
        Route::post('shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');
        Route::resource('cash-registers', CashRegisterController::class)->except(['show', 'edit', 'update', 'create']);
        Route::resource('sales-returns', SaleReturnController::class)->only(['index', 'store', 'show']);
        Route::get('sales-returns/create/{sale}', [SaleReturnController::class, 'createFromSale'])->name('sales-returns.create-from-sale');
    });

    // Merchandise
    Route::prefix('merchandise')->name('merchandise.')->group(function () {
        Route::resource('promotions', PromotionController::class)->except(['show']);
        Route::resource('members', MemberController::class)->except(['edit', 'update', 'create']);
    });

    // Finance
    Route::prefix('finance')->name('finance.')->middleware('permission:expenses.manage')->group(function () {
        Route::resource('expenses', ExpenseController::class)->except(['show', 'edit', 'update', 'create']);
        Route::post('expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');
        Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show', 'edit', 'update', 'create']);
    });

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function () {
        Route::get('sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('sales/print', [ReportController::class, 'salesPrint'])->name('sales.print');
        Route::get('sales/{sale}', [ReportController::class, 'salesDetail'])->name('sales.detail');
        Route::get('purchases', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('purchases/print', [ReportController::class, 'purchasesPrint'])->name('purchases.print');
        Route::get('stocks', [ReportController::class, 'stocks'])->name('stocks');
        Route::get('stocks/print', [ReportController::class, 'stocksPrint'])->name('stocks.print');
        Route::get('profit', [ReportController::class, 'profit'])->name('profit');
        Route::get('profit/print', [ReportController::class, 'profitPrint'])->name('profit.print');
        Route::get('moving-stock', [ReportController::class, 'movingStock'])->name('moving-stock');
        Route::get('moving-stock/print', [ReportController::class, 'movingStockPrint'])->name('moving-stock.print');
        Route::get('purchase-returns', [ReportController::class, 'purchaseReturns'])->name('purchase-returns');
        Route::get('purchase-returns/print', [ReportController::class, 'purchaseReturnsPrint'])->name('purchase-returns.print');
        Route::get('purchase-receivings', [ReportController::class, 'purchaseReceivings'])->name('purchase-receivings');
        Route::get('purchase-receivings/print', [ReportController::class, 'purchaseReceivingsPrint'])->name('purchase-receivings.print');
        Route::get('product-margin', [ReportController::class, 'productMargin'])->name('product-margin');
        Route::get('ppn', [ReportController::class, 'ppn'])->name('ppn');
        Route::resource('sales-targets', SalesTargetController::class);
        Route::get('sales-target-report', [SalesTargetController::class, 'report'])->name('sales-targets.report');
    });

    // Barcode lookup API
    Route::get('api/products/barcode/{barcode}', [ProductController::class, 'getByBarcode'])->name('api.products.barcode');

    // Purchase Order Payments
    Route::get('purchasing/orders/{order}/payment', [PurchaseOrderController::class, 'payment'])->name('purchasing.orders.payment');
    Route::post('purchasing/orders/{order}/payment', [PurchaseOrderController::class, 'recordPayment'])->name('purchasing.orders.recordPayment');

    // Member Credits
    Route::prefix('merchandise')->name('merchandise.')->group(function () {
        Route::get('credits', [MemberCreditController::class, 'index'])->name('credits.index');
        Route::get('members/{member}/credit', [MemberCreditController::class, 'create'])->name('members.credit');
        Route::post('members/{member}/credit', [MemberCreditController::class, 'store'])->name('members.credit.store');
        Route::get('members/{member}/credit-history', [MemberCreditController::class, 'history'])->name('members.credit.history');
    });

    // Price History
    Route::get('master/products/{product}/price-history', [ProductController::class, 'priceHistory'])->name('master.products.price-history');

    // Settings
    Route::prefix('settings')->name('settings.')->middleware('permission:settings.manage')->group(function () {
        Route::get('company', [CompanyController::class, 'index'])->name('company.index');
        Route::post('company', [CompanyController::class, 'update'])->name('company.update');
        Route::resource('users', UserController::class)->except(['show', 'edit', 'create']);
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
        Route::resource('gift-cards', GiftCardController::class);
        Route::post('gift-cards/{giftCard}/top-up', [GiftCardController::class, 'topUp'])->name('gift-cards.topUp');
    });
});
