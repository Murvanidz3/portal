<?php

namespace App\Providers;

use App\Models\GodModePermission;
use App\Models\GodModeStyle;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class GodModeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind GodModeService as singleton
        $this->app->singleton(\App\Services\GodModeService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share styles with all views (except god-mode views)
        View::composer('*', function ($view) {
            // Skip god-mode views to avoid recursion
            if (str_starts_with($view->getName(), 'god-mode')) {
                return;
            }

            try {
                // Get CSS variables
                $godModeStyles = GodModeStyle::generateCssVariables();
                $view->with('godModeStyles', $godModeStyles);

                // Get branding
                $godModeBranding = GodModeStyle::getBranding();
                $view->with('godModeBranding', $godModeBranding);
            } catch (\Exception $e) {
                // If tables don't exist yet, use empty defaults
                $view->with('godModeStyles', '');
                $view->with('godModeBranding', []);
            }
        });

        // Register Blade directives for permission checking
        Blade::directive('godcan', function ($expression) {
            return "<?php if(\App\Models\GodModePermission::isEnabled($expression, auth()->user()->role ?? 'client')): ?>";
        });

        Blade::directive('endgodcan', function () {
            return "<?php endif; ?>";
        });

        // Directive to check global feature
        Blade::directive('godfeature', function ($expression) {
            return "<?php if(\App\Models\GodModePermission::isEnabled($expression)): ?>";
        });

        Blade::directive('endgodfeature', function () {
            return "<?php endif; ?>";
        });

        // Directive to output CSS variables
        Blade::directive('godstyles', function () {
            return "<?php echo '<style>' . \App\Models\GodModeStyle::generateCssVariables() . '</style>'; ?>";
        });

        // Directive to get a specific style value
        Blade::directive('godstyle', function ($expression) {
            return "<?php echo \App\Models\GodModeStyle::getValue($expression); ?>";
        });
    }
}
