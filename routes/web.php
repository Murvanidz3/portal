<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

// Logout (available for authenticated users)
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Cars
    Route::resource('cars', CarController::class);
    Route::patch('cars/{car}/status', [CarController::class, 'updateStatus'])->name('cars.update-status');
    Route::patch('cars/{car}/recipient', [CarController::class, 'updateRecipient'])->name('cars.update-recipient');
    Route::delete('cars/{car}/files/{file}', [CarController::class, 'deleteFile'])->name('cars.delete-file');
    Route::post('cars/{car}/files/{file}/main', [CarController::class, 'setMainPhoto'])->name('cars.set-main-photo');
    Route::post('cars/{car}/files/bulk-delete', [CarController::class, 'bulkDeleteFiles'])->name('cars.bulk-delete-files');
    Route::get('cars/{car}/invoice/{type}', [CarController::class, 'invoice'])->name('cars.invoice');

    // Finance
    Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('finance/{dealer}', [FinanceController::class, 'show'])->name('finance.show');

    // Wallet
    Route::get('wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('wallet/create', [WalletController::class, 'create'])->name('wallet.create')->middleware('role:admin');
    Route::post('wallet', [WalletController::class, 'store'])->name('wallet.store')->middleware('role:admin');
    Route::get('wallet/{user}', [WalletController::class, 'show'])->name('wallet.show');
    Route::post('wallet/transfer-wallet-to-car', [WalletController::class, 'transferWalletToCar'])->name('wallet.transfer-wallet-to-car');
    Route::post('wallet/transfer-car-to-car', [WalletController::class, 'transferCarToCar'])->name('wallet.transfer-car-to-car');

    // Transactions
    Route::resource('transactions', TransactionController::class);

    // Invoices
    Route::resource('invoices', InvoiceController::class)->except(['edit', 'update']);
    Route::get('invoices/car/{car}/data', [InvoiceController::class, 'getCarData'])->name('invoices.car-data');
    Route::get('invoices/car/{car}/generate/{type}', [InvoiceController::class, 'generateFromCar'])->name('invoices.generate-from-car');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('notifications/recent', [NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::get('notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('notifications/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Calculator
    Route::get('calculator', [CalculatorController::class, 'index'])->name('calculator.index');
    Route::post('calculator/calculate', [CalculatorController::class, 'calculate'])->name('calculator.calculate');
    Route::post('calculator/shipping', [CalculatorController::class, 'calculateShipping'])->name('calculator.shipping');
    Route::get('calculator/locations', [CalculatorController::class, 'searchLocations'])->name('calculator.locations');
    Route::get('calculator/rates-info', [CalculatorController::class, 'hasCustomRates'])->name('calculator.rates-info');
    Route::get('calculator/get-locations', [CalculatorController::class, 'getLocations'])->name('calculator.get-locations');
    Route::post('calculator/calculate-from-rates', [CalculatorController::class, 'calculateShippingFromRates'])->name('calculator.calculate-from-rates');

    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/change-password', [ProfileController::class, 'showChangePassword'])->name('profile.change-password');
    Route::post('profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password.update');
    Route::get('profile/stats', [ProfileController::class, 'stats'])->name('profile.stats');

    // Admin only routes
    Route::middleware(['role:admin'])->group(function () {
        // Users
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-approval', [UserController::class, 'toggleApproval'])->name('users.toggle-approval');
        Route::post('users/{user}/toggle-sms', [UserController::class, 'toggleSms'])->name('users.toggle-sms');
        Route::post('users/{user}/update-balance', [UserController::class, 'updateBalance'])->name('users.update-balance');

        // SMS Management
        Route::get('sms', [SmsController::class, 'index'])->name('sms.index');
        Route::post('sms/send', [SmsController::class, 'send'])->name('sms.send');
        Route::post('sms/send-to-user', [SmsController::class, 'sendToUser'])->name('sms.send-to-user');
        Route::post('sms/send-for-car/{car}', [SmsController::class, 'sendForCar'])->name('sms.send-for-car');
        Route::post('sms/send-bulk', [SmsController::class, 'sendBulk'])->name('sms.send-bulk');
        Route::put('sms/templates/{template}', [SmsController::class, 'updateTemplate'])->name('sms.update-template');
        Route::delete('sms/logs/{log}', [SmsController::class, 'deleteLog'])->name('sms.delete-log');
        Route::post('sms/clear-old-logs', [SmsController::class, 'clearOldLogs'])->name('sms.clear-old-logs');
        Route::post('sms/clear-all-logs', [SmsController::class, 'clearAllLogs'])->name('sms.clear-all-logs');

        // Notifications (admin sending)
        Route::post('notifications/send', [NotificationController::class, 'send'])->name('notifications.send');
        Route::post('notifications/send-bulk', [NotificationController::class, 'sendBulk'])->name('notifications.send-bulk');
        Route::post('notifications/send-to-all-dealers', [NotificationController::class, 'sendToAllDealers'])->name('notifications.send-to-all-dealers');

        // Settings
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('settings/single', [SettingsController::class, 'updateSingle'])->name('settings.update-single');
        Route::post('settings/toggle-maintenance', [SettingsController::class, 'toggleMaintenance'])->name('settings.toggle-maintenance');
        Route::post('settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
        Route::get('settings/system-info', [SettingsController::class, 'systemInfo'])->name('settings.system-info');
    });
});

// Uploads route - serve files from uploads directory
Route::get('/uploads/{path}', function ($path) {
    // Try storage path first
    $filePath = storage_path('app/public/uploads/' . $path);

    // Fallback to old uploads location in backupv1
    if (!file_exists($filePath)) {
        $filePath = base_path('backupv1/uploads/' . $path);
    }

    // Final fallback
    if (!file_exists($filePath)) {
        $filePath = base_path('backupv1/public/uploads/' . $path);
    }

    if (!file_exists($filePath)) {
        abort(404);
    }

    return response()->file($filePath);
})->where('path', '.*')->name('uploads');

/*
|--------------------------------------------------------------------------
| God Mode Routes (Super Admin)
|--------------------------------------------------------------------------
| Completely isolated authentication and management system.
| These routes use the 'god' guard and are separate from the main CRM.
*/

use App\Http\Controllers\GodMode\AuthController as GodAuthController;
use App\Http\Controllers\GodMode\DashboardController as GodDashboardController;
use App\Http\Controllers\GodMode\PermissionController as GodPermissionController;
use App\Http\Controllers\GodMode\StyleController as GodStyleController;

Route::prefix('god')->name('god.')->group(function () {
    // Guest routes (login)
    Route::middleware('guest:god')->group(function () {
        Route::get('login', [GodAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [GodAuthController::class, 'login'])->name('login.submit');
    });

    // Authenticated God Mode routes
    Route::middleware(\App\Http\Middleware\GodModeAuth::class)->group(function () {
        // Logout
        Route::post('logout', [GodAuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/', [GodDashboardController::class, 'index'])->name('dashboard');
        Route::get('audit-logs', [GodDashboardController::class, 'auditLogs'])->name('audit-logs');

        // Permissions
        Route::get('permissions', [GodPermissionController::class, 'index'])->name('permissions');
        Route::patch('permissions/{permission}', [GodPermissionController::class, 'update'])->name('permissions.update');
        Route::post('permissions/bulk-update', [GodPermissionController::class, 'bulkUpdate'])->name('permissions.bulk-update');
        Route::post('permissions/reset', [GodPermissionController::class, 'reset'])->name('permissions.reset');

        // Styles
        Route::get('styles', [GodStyleController::class, 'index'])->name('styles');
        Route::patch('styles/{style}/color', [GodStyleController::class, 'updateColor'])->name('styles.update-color');
        Route::patch('styles/{style}/text', [GodStyleController::class, 'updateText'])->name('styles.update-text');
        Route::post('styles/{style}/image', [GodStyleController::class, 'uploadImage'])->name('styles.upload-image');
        Route::post('styles/{style}/reset', [GodStyleController::class, 'resetToDefault'])->name('styles.reset');
        Route::post('styles/reset-all', [GodStyleController::class, 'resetAll'])->name('styles.reset-all');
        Route::get('styles/css', [GodStyleController::class, 'getCss'])->name('styles.css');

        // Shipping Rates (User-specific Excel uploads)
        Route::prefix('shipping-rates')->name('shipping-rates.')->group(function () {
            Route::get('/', [\App\Http\Controllers\GodMode\ShippingRatesController::class, 'index'])->name('index');
            Route::get('/template', [\App\Http\Controllers\GodMode\ShippingRatesController::class, 'downloadTemplate'])->name('template');
            Route::get('/{user}', [\App\Http\Controllers\GodMode\ShippingRatesController::class, 'show'])->name('show');
            Route::post('/{user}/upload', [\App\Http\Controllers\GodMode\ShippingRatesController::class, 'upload'])->name('upload');
            Route::get('/{user}/search', [\App\Http\Controllers\GodMode\ShippingRatesController::class, 'searchLocations'])->name('search');
            Route::post('/file/{file}/activate', [\App\Http\Controllers\GodMode\ShippingRatesController::class, 'activate'])->name('activate');
            Route::delete('/file/{file}', [\App\Http\Controllers\GodMode\ShippingRatesController::class, 'delete'])->name('delete');
        });
    });
});

