<?php

namespace App\Providers;

use App\Models\Transaction;
use App\Models\Car;
use App\Observers\TransactionObserver;
use App\Observers\CarObserver;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use App\Services\SmsService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services as singletons
        $this->app->singleton(SmsService::class, function ($app) {
            return new SmsService();
        });

        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService($app->make(SmsService::class));
        });

        $this->app->singleton(FileUploadService::class, function ($app) {
            return new FileUploadService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        Transaction::observe(TransactionObserver::class);
        Car::observe(CarObserver::class);
        
        // Custom Blade directives
        Blade::directive('money', function ($amount) {
            return "<?php echo '$' . number_format($amount, 2); ?>";
        });

        Blade::directive('date', function ($date) {
            return "<?php echo $date ? $date->format('d.m.Y') : '-'; ?>";
        });

        // Share common data with all views
        view()->composer('layouts.partials.header', function ($view) {
            if (auth()->check()) {
                $view->with('unreadNotifications', auth()->user()->getUnreadNotificationsCount());
            }
        });
    }
}
