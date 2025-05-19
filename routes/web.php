<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Redirect root ('/') to the login page if the user is not authenticated.
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard'); // If the user is authenticated, redirect to the home page.
    }
    return redirect('/login'); // If the user is not authenticated, redirect to the login page.
});

// Forgot Password Routes
Route::get('password/reset', 'App\Http\Controllers\Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'App\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');

// Reset Password Routes
Route::get('password/reset/{token}', 'App\Http\Controllers\Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'App\Http\Controllers\Auth\ResetPasswordController@reset')->name('password.update');


Auth::routes();
Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
// Route::post('/select-role', [App\Http\Controllers\Auth\LoginController::class, 'selectRole'])->name('select-role');
Route::post('/get-user-roles', [App\Http\Controllers\Auth\LoginController::class, 'getUserRoles'])->name('get-user-roles');
Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');
Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index']);

//Apply the change deafault password middleware to relevant routes
use App\Http\Controllers\DashboardController;
Route::middleware(['auth', 'check.default_password'])->group(function () {
    Route::resource('dashboard', DashboardController::class);
    Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
});



use App\Http\Controllers\RegisterAccountController;
Route::resource('register_account', RegisterAccountController::class);
Route::get('create', [RegisterAccountController::class, 'create'])->name('createAccount');

use App\Http\Controllers\AccountManagementController;
Route::resource('account_management', AccountManagementController::class);
Route::get('confirm-email/{id}', [AccountManagementController::class, 'confirmEmail'])->name('confirm.email');
Route::post('/resend-confirmation/{id}', [AccountManagementController::class, 'resendConfirmationEmail'])->name('resend_confirmation_email');
Route::post('confirm_account/{id}', [AccountManagementController::class, 'confirmAccount'])->name('confirm_account');
Route::post('reject_account/{id}', [AccountManagementController::class, 'rejectAccount'])->name('reject_account');
Route::get('accounts_table', [AccountManagementController::class, 'index'])->name('accounts_table');
Route::get('create', [AccountManagementController::class, 'create'])->name('create_account');
Route::post('update_role/{id}', [AccountManagementController::class, 'updateRole'])->name('update_role');
Route::delete('delete/{id}', [AccountManagementController::class, 'destroy'])->name('delete_account');

//filter accounts
Route::get('/account-management/confirm_reject_filter', [AccountManagementController::class, 'confirmRejectFilter'])->name('accounts_table.confirm_reject_filter');
Route::get('/account-management/resend_link_filter', [AccountManagementController::class, 'resendLinkFilter'])->name('accounts_table.resend_link_filter');


// for change default password
Route::get('/change-password', [AccountManagementController::class, 'changePassword'])->name('password.change');
Route::post('/change-password', [AccountManagementController::class, 'updatePassword'])->name('password_update');


use App\Http\Controllers\ProfileController;
Route::resource('profile', ProfileController::class);
Route::put('/profile/update/{field}', [ProfileController::class, 'update'])->name('profile.update'); //new

Route::get('show_profile', [ProfileController::class, 'show'])->name('show_profile');
Route::get('edit_profile/{id}', [ProfileController::class, 'edit'])->name('edit_profile');
// Route::put('update_profile/{id}', [ProfileController::class, 'update'])->name('update_profile');

use App\Http\Controllers\InventoryController;
Route::resource('inventory', InventoryController::class);
Route::get('inventory_table', [InventoryController::class, 'index'])->name('inventory_table');
Route::get('inventory_products_table', [InventoryController::class, 'inventoryProductsTable'])->name('inventory_products_table');
Route::get('inventory_edit_product/{id}', [InventoryController::class, 'edit'])->name('inventory_edit_product');
Route::post('inventory_update_product/{id}', [InventoryController::class, 'update'])->name('inventory_update_product');
Route::post('low_stock', [InventoryController::class, 'LowStock'])->name('low_stock');
Route::delete('inventory_delete_product/{id}', [InventoryController::class, 'destroy'])->name('inventory_delete_product');
// Route::delete('delete/{id}', [InventoryController::class, 'destroy'])->name('delete_product');

//filter inventory products table
Route::get('inventory_filter_product_name', [InventoryController::class, 'inventoryFilterProductName'])->name('inventory_filter_product_name');
Route::get('inventory_filter_category', [InventoryController::class, 'inventoryFilterCategory'])->name('inventory_filter_category');
Route::get('inventory_filter_supplier', [InventoryController::class, 'inventoryFilterSupplier'])->name('inventory_filter_supplier');

//filter inventory
Route::get('product_name_filter', [InventoryController::class, 'productNameFilter'])->name('product_name_filter');
Route::get('category_filter', [InventoryController::class, 'CategoryFilter'])->name('category_filter');
Route::get('supplier_filter', [InventoryController::class, 'supplierFilter'])->name('supplier_filter');

use App\Http\Controllers\PurchaseController;
use App\Models\PurchaseOrder;

    Route::resource('purchase', PurchaseController::class);
    Route::get('purchase_table', [PurchaseController::class, 'index'])->name('purchase_table');
    Route::post('details', [PurchaseController::class, 'getSupplierDetails']);
    Route::get('create/{id}', [PurchaseController::class, 'create'])->name('create_product');
    Route::post('store/{id}/product', [PurchaseController::class, 'store'])->name('store_product');
    Route::post('restock', [PurchaseController::class, 'restock'])->name('restock_product');
    Route::post('restock_store_product', [PurchaseController::class, 'restockStoreProduct'])->name('restock_store_product');
    Route::get('/edit_product/{supplier_id}/{product_id}', [PurchaseController::class, 'edit'])->name('edit_product');
    Route::post('update_product/{supplier_id}/{product_id}', [PurchaseController::class, 'update'])->name('update_product');
    Route::delete('delete/{supplier_id}/{product_id}', [PurchaseController::class, 'destroy'])->name('delete_product');
    Route::get('purchase_order',[PurchaseController::class, 'order_list'])->name('purchase_order');
    Route::get('create_purchase_order', [PurchaseController::class, 'create_purchase_order'])->name('create_purchase_order');
    Route::get('supplier/{supplierId}/products', [PurchaseController::class, 'getProductsBySupplier']);
    Route::get('supplier/{supplierId}', [PurchaseController::class, 'getSupplierAddress']); // Get supplier address at create_purchase_order
    Route::post('store/order', [PurchaseController::class, 'storeOrder'])->name('store_order');
    Route::get('edit_purchase_order/{id}', [PurchaseController::class, 'editOrder'])->name('edit_order');
    Route::post('update_order/{id}', [PurchaseController::class, 'update_order'])->name('update_order');
    Route::delete('purchase_order/{id}',[PurchaseController::class, 'destroyOrder'])->name('delete_order');
    Route::post('purchase_order/update_order_status', [PurchaseController::class, 'updateOrderStatus'])->name('update_order_status');
    Route::post('/purchase_order/change-status', [PurchaseController::class, 'updateStatusToOrdered'])->name('change_status_to_order');
    Route::post('/purchase_order/update_order_changes', [PurchaseController::class, 'updateOrderChanges'])->name('update_order_changes');
    Route::post('/purchase_order/create_backorder_request', [PurchaseController::class, 'createBackorderRequest'])->name('create_backorder_request');
    Route::get('suppliers_table', [PurchaseController::class, 'supplier_list'])->name('supplier_list');
    Route::get('suppliers_table/create_supplier', [PurchaseController::class, 'create_supplier'])->name('create_supplier');
    Route::post('suppliers_table/create_supplier/store', [PurchaseController::class, 'store_supplier'])->name('store_supplier');
    Route::get('suppliers_table/{id}/supplier_info', [PurchaseController::class, 'supplier_info'])->name('supplier_info');
    Route::get('suppliers_table/{id}/edit_supplier', [PurchaseController::class, 'edit_supplier'])->name('edit_supplier');
    Route::post('suppliers_table/{id}/update', [PurchaseController::class, 'update_supplier'])->name('update_supplier');
    Route::delete('suppliers_table/{id}/delete',[PurchaseController::class, 'delete_supplier'])->name('delete_supplier');
    Route::get('/create-reorder/{supplier_id}', [PurchaseController::class, 'create_reorder'])->name('create_reorder');




//filter products table
Route::get('filter_product_name', [PurchaseController::class, 'productNameFilter'])->name('filter_product_name');
Route::get('filter_category', [PurchaseController::class, 'CategoryFilter'])->name('filter_category');
Route::get('filter_category2/{supplierId}', [PurchaseController::class, 'CategoryFilter2'])->name('filter_category_supplier_info');
Route::get('filter_supplier', [PurchaseController::class, 'supplierFilter'])->name('filter_supplier');
Route::get('filter_store_restock', [PurchaseController::class, 'storeRestockFilter'])->name('filter_store_restock');
Route::get('filter_stockroom_restock', [PurchaseController::class, 'stockroomRestockFilter'])->name('filter_stockroom_restock');

//filter orders
Route::get('order_product_filter', [PurchaseController::class, 'orderProductFilter'])->name('order_product_filter');
Route::get('order_supplier_filter', [PurchaseController::class, 'orderSupplierFilter'])->name('order_supplier_filter');
Route::get('created_by_filter', [PurchaseController::class, 'createdByFilter'])->name('created_by_filter');
Route::get('payment_method_filter', [PurchaseController::class, 'paymentMethodFilter'])->name('payment_method_filter');
Route::get('order_status_filter', [PurchaseController::class, 'orderStatusFilter'])->name('order_status_filter');


use App\Http\Controllers\SalesController;
Route::resource('sales', SalesController::class);
Route::get('sales.dashboard', [SalesController::class, 'dashboard'])->name('sales.dashboard');
Route::post('sales', [SalesController::class, 'store'])->name('sales.store');
Route::get('sales_table', [SalesController::class, 'index'])->name('sales_table');
Route::get('create', [SalesController::class, 'create'])->name('sale_product');
Route::post('fetch-product', [SalesController::class, 'fetchProduct'])->name('fetch.product');
Route::get('search', [SalesController::class, 'search'])->name('sales.search');
Route::get('product_sale_price_table', [SalesController::class, 'producSalePriceTable'])->name('product_sale_price_table');
Route::post('product_sale_price', [SalesController::class, 'productSalePrice'])->name('product_sale_price');
Route::get('pos_terminal', [SalesController::class, 'create'])->name('sale_product');
Route::get('/sales/table', [SalesController::class, 'salesTable'])->name('sales.table');
Route::get('/sales/{id}/receipt', [SalesController::class, 'showReceipt'])
     ->name('sales.receipt')
     ->middleware('auth');

//filter sale price table
Route::get('filter_product_name_sale', [SalesController::class, 'productNameFilterSale'])->name('filter_product_name_sale');
Route::get('filter_price_low_to_high', [SalesController::class, 'filterPriceLowToHigh'])->name('filter_price_low_to_high');


use App\Http\Controllers\ReturnProductController;
Route::get('return_product', [ReturnProductController::class, 'index'])->name('return_product_table');
Route::get('return_product/{id}', [ReturnProductController::class, 'showReturnForm'])->name('return_product.show');
Route::post('return_product/{id}', [ReturnProductController::class, 'processReturn'])->name('return_product.process');

use App\Http\Controllers\InventoryAuditController;
Route::resource('inventory_audit', InventoryAuditController::class);
Route::get('audit_inventory_table', [InventoryAuditController::class, 'index'])->name('audit_inventory_table');
Route::post('update/{inventory_id}', [InventoryAuditController::class, 'update'])->name('inventory.audit.update');
Route::get('logs', [InventoryAuditController::class, 'logs'])->name('inventory.audit.logs');
Route::post('find_discrepancies', [InventoryAuditController::class, 'findDiscrepancies'])->name('find_discrepancies');

//filter audit inventory
Route::get('filter_audit_product_name', [InventoryAuditController::class, 'productNameFilter'])->name('filter_audit_product_name');
Route::get('filter_audit_category', [InventoryAuditController::class, 'CategoryFilter'])->name('filter_audit_category');
Route::get('filter_audit_supplier', [InventoryAuditController::class, 'supplierFilter'])->name('filter_audit_supplier');


//filter audit logs
Route::get('filter_auditor', [InventoryAuditController::class, 'auditorFilter'])->name('filter_auditor');
Route::get('filter_discrepancy_reason', [InventoryAuditController::class, 'discrepancyReasonFilter'])->name('filter_discrepancy_reason');
Route::get('filter_date_audited', [InventoryAuditController::class, 'dateAuditedFilter'])->name('filter_date_audited');

use App\Http\Controllers\ReportController;
//filtered purchase report
Route::post('purchased_report', [ReportController::class, 'generatePurchasedReport'])->name('report.generate_purchased');
Route::post('generate_purchased_filter_report', [ReportController::class, 'generatePurchasedFilteredReport'])->name('generate_purchased_filter_report');

//filtered inentory report
Route::post('inventory_report', [ReportController::class, 'generateReport'])->name('report.generate');
Route::post('generate_filter_report', [ReportController::class, 'generateFilteredReport'])->name('generate_filter_report');

//filtered audit inventory report
Route::post('audit_inventory_report', [ReportController::class, 'generateAuditReport'])->name('audit.report.generate');
Route::post('generate_audit_filter_report', [ReportController::class, 'generateAuditFilteredReport'])->name('generate_audit_filter_report');
Route::post('/upload-signature', [ReportController::class, 'uploadSignature'])->name('upload.signature');


use App\Http\Controllers\ScrapController;
Route::post('dispose', [ScrapController::class, 'disposeProduct'])->name('dispose_product');

