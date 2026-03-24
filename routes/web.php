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
Route::middleware(['auth', 'approved'])->group(function () {

    // Dashboard (always accessible)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Cars - view routes (all authenticated+approved users including clients)
    Route::middleware(['god.permission:cars.access'])->group(function () {
        Route::get('cars', [CarController::class, 'index'])->name('cars.index');
        Route::get('cars/{car}', [CarController::class, 'show'])->name('cars.show');
        Route::get('cars/{car}/invoice/{type}', [CarController::class, 'invoice'])->name('cars.invoice');
    });

    // Cars - create routes (admin only)
    Route::middleware(['role:admin', 'god.permission:cars.create'])->group(function () {
        Route::get('cars/create', [CarController::class, 'create'])->name('cars.create');
        Route::post('cars', [CarController::class, 'store'])->name('cars.store');
    });

    // Cars - edit routes (admin only)
    Route::middleware(['role:admin', 'god.permission:cars.edit'])->group(function () {
        Route::get('cars/{car}/edit', [CarController::class, 'edit'])->name('cars.edit');
        Route::put('cars/{car}', [CarController::class, 'update'])->name('cars.update');
        Route::patch('cars/{car}/status', [CarController::class, 'updateStatus'])->name('cars.update-status');
        Route::patch('cars/{car}/recipient', [CarController::class, 'updateRecipient'])->name('cars.update-recipient');
        Route::delete('cars/{car}/files/{file}', [CarController::class, 'deleteFile'])->name('cars.delete-file');
        Route::post('cars/{car}/files/{file}/main', [CarController::class, 'setMainPhoto'])->name('cars.set-main-photo');
        Route::post('cars/{car}/files/bulk-delete', [CarController::class, 'bulkDeleteFiles'])->name('cars.bulk-delete-files');
    });

    // Cars - delete (admin only)
    Route::delete('cars/{car}', [CarController::class, 'destroy'])->name('cars.destroy')->middleware(['role:admin', 'god.permission:cars.delete']);

    // Finance - admin and dealer
    Route::middleware(['role:admin,dealer', 'god.permission:finance.access'])->group(function () {
        Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('finance/{dealer}', [FinanceController::class, 'show'])->name('finance.show');
    });

    // Wallet - admin and dealer
    Route::middleware(['role:admin,dealer', 'god.permission:wallet.access'])->group(function () {
        Route::get('wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::get('wallet/create', [WalletController::class, 'create'])->name('wallet.create')->middleware('role:admin');
        Route::post('wallet', [WalletController::class, 'store'])->name('wallet.store')->middleware('role:admin');
        Route::get('wallet/{user}', [WalletController::class, 'show'])->name('wallet.show');
    });

    // Wallet - transfers
    Route::middleware(['role:admin,dealer', 'god.permission:wallet.transfer'])->group(function () {
        Route::post('wallet/transfer-wallet-to-car', [WalletController::class, 'transferWalletToCar'])->name('wallet.transfer-wallet-to-car');
        Route::post('wallet/transfer-car-to-car', [WalletController::class, 'transferCarToCar'])->name('wallet.transfer-car-to-car');
    });

    // Transactions - admin and dealer
    Route::middleware(['role:admin,dealer'])->group(function () {
        Route::middleware(['god.permission:transactions.access'])->group(function () {
            Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
            Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        });
        Route::middleware(['god.permission:transactions.create'])->group(function () {
            Route::get('transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
            Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');
        });
        Route::middleware(['god.permission:transactions.edit'])->group(function () {
            Route::get('transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
            Route::put('transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
        });
        Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy')->middleware('god.permission:transactions.delete');
    });

    // Invoices - admin and dealer
    Route::middleware(['role:admin,dealer'])->group(function () {
        Route::middleware(['god.permission:invoices.access'])->group(function () {
            Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
            Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        });
        Route::middleware(['god.permission:invoices.create'])->group(function () {
            Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
            Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
            Route::get('invoices/car/{car}/data', [InvoiceController::class, 'getCarData'])->name('invoices.car-data');
            Route::get('invoices/car/{car}/generate/{type}', [InvoiceController::class, 'generateFromCar'])->name('invoices.generate-from-car');
        });
        Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    });

    // Calculator - admin and dealer
    Route::middleware(['role:admin,dealer', 'god.permission:calculator.access'])->group(function () {
        Route::get('calculator', [CalculatorController::class, 'index'])->name('calculator.index');
        Route::post('calculator/calculate', [CalculatorController::class, 'calculate'])->name('calculator.calculate');
        Route::get('calculator/get-locations', [CalculatorController::class, 'getLocations'])->name('calculator.get-locations');
        Route::post('calculator/calculate-from-rates', [CalculatorController::class, 'calculateShippingFromRates'])->name('calculator.calculate-from-rates');
    });

    // Notifications - all roles
    Route::middleware(['god.permission:notifications.access'])->group(function () {
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
        Route::get('notifications/recent', [NotificationController::class, 'getRecent'])->name('notifications.recent');
        Route::get('notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
        Route::post('notifications/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
        Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    });

    // Notifications - admin sending
    Route::middleware(['role:admin', 'god.permission:notifications.send'])->group(function () {
        Route::post('notifications/send', [NotificationController::class, 'send'])->name('notifications.send');
        Route::post('notifications/send-bulk', [NotificationController::class, 'sendBulk'])->name('notifications.send-bulk');
        Route::post('notifications/send-to-all-dealers', [NotificationController::class, 'sendToAllDealers'])->name('notifications.send-to-all-dealers');
    });

    // Profile (always accessible - no permission gate)
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/change-password', [ProfileController::class, 'showChangePassword'])->name('profile.change-password');
    Route::post('profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password.update');
    Route::get('profile/stats', [ProfileController::class, 'stats'])->name('profile.stats');

    // Admin only routes
    Route::middleware(['role:admin'])->group(function () {
        // Users
        Route::middleware(['god.permission:users.access'])->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        });
        Route::middleware(['god.permission:users.create'])->group(function () {
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
        });
        Route::middleware(['god.permission:users.edit'])->group(function () {
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::post('users/{user}/toggle-approval', [UserController::class, 'toggleApproval'])->name('users.toggle-approval');
            Route::post('users/{user}/toggle-sms', [UserController::class, 'toggleSms'])->name('users.toggle-sms');
            Route::post('users/{user}/update-balance', [UserController::class, 'updateBalance'])->name('users.update-balance');
        });
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('god.permission:users.delete');

        // SMS Management
        Route::middleware(['god.permission:sms.access'])->group(function () {
            Route::get('sms', [SmsController::class, 'index'])->name('sms.index');
            Route::put('sms/templates/{template}', [SmsController::class, 'updateTemplate'])->name('sms.update-template');
            Route::delete('sms/logs/{log}', [SmsController::class, 'deleteLog'])->name('sms.delete-log');
            Route::post('sms/clear-old-logs', [SmsController::class, 'clearOldLogs'])->name('sms.clear-old-logs');
            Route::post('sms/clear-all-logs', [SmsController::class, 'clearAllLogs'])->name('sms.clear-all-logs');
        });
        Route::middleware(['god.permission:sms.send'])->group(function () {
            Route::post('sms/send', [SmsController::class, 'send'])->name('sms.send');
            Route::post('sms/send-to-user', [SmsController::class, 'sendToUser'])->name('sms.send-to-user');
            Route::post('sms/send-for-car/{car}', [SmsController::class, 'sendForCar'])->name('sms.send-for-car');
            Route::post('sms/send-bulk', [SmsController::class, 'sendBulk'])->name('sms.send-bulk');
        });

        // Settings
        Route::middleware(['god.permission:settings.access'])->group(function () {
            Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::get('settings/system-info', [SettingsController::class, 'systemInfo'])->name('settings.system-info');
        });
        Route::middleware(['god.permission:settings.edit'])->group(function () {
            Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
            Route::post('settings/single', [SettingsController::class, 'updateSingle'])->name('settings.update-single');
            Route::post('settings/toggle-maintenance', [SettingsController::class, 'toggleMaintenance'])->name('settings.toggle-maintenance');
            Route::post('settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
        });
    });
});

// Uploads route - serve files from uploads directory (auth required)
Route::get('/uploads/{path}', function ($path) {
    // Path traversal prevention
    $path = ltrim($path, '/');
    if (str_contains($path, '..') || str_contains($path, "\0") || str_contains($path, '//')) {
        abort(403);
    }

    // Only serve from storage/app/public/uploads - no backupv1 fallback
    $filePath = storage_path('app/public/uploads/' . $path);

    if (!file_exists($filePath) || !is_file($filePath)) {
        abort(404);
    }

    // MIME type whitelist - only allow safe file types
    $allowedMimes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/webm', 'video/quicktime',
        'application/pdf',
    ];

    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($filePath);

    if (!in_array($mime, $allowedMimes)) {
        abort(403);
    }

    return response()->file($filePath);
})->where('path', '[a-zA-Z0-9/_\-\.]+')->middleware('auth')->name('uploads');

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
use App\Http\Controllers\GodMode\ShippingRatesController as GodShippingRatesController;

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

        // Shipping Rates
        Route::get('shipping-rates', [GodShippingRatesController::class, 'index'])->name('shipping-rates');
        Route::post('shipping-rates/upload', [GodShippingRatesController::class, 'upload'])->name('shipping-rates.upload');
        Route::get('shipping-rates/download', [GodShippingRatesController::class, 'download'])->name('shipping-rates.download');
        Route::get('shipping-rates/preview', [GodShippingRatesController::class, 'preview'])->name('shipping-rates.preview');


    });
});

