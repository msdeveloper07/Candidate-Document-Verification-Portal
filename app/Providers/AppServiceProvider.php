<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
         * On shared hosting the web root often cannot sit inside the project —
         * the framework files live outside public_html while only the contents
         * of public/ are served. Point Laravel at the real web root so asset()
         * and storage:link resolve correctly.
         */
        if ($path = env('PUBLIC_PATH')) {
            $this->app->usePublicPath(
                str_starts_with($path, '/') ? $path : base_path($path)
            );
        }
    }

    public function boot(): void
    {
        // Views use Bootstrap 5, so the paginator should too.
        Paginator::useBootstrapFive();

        Schema::defaultStringLength(191);

        $this->applyStoredSettings();
    }

    /**
     * Values an admin edits in the database win over the .env defaults.
     * Wrapped because this runs before migrations exist on a fresh install.
     */
    private function applyStoredSettings(): void
    {
        if ($this->app->runningInConsole() && ! $this->app->environment('testing')) {
            // Skip during migrate/seed so a missing table never blocks the command.
            return;
        }

        try {
            $settings = Setting::cached();
        } catch (Throwable) {
            return;
        }

        if (filled($settings['agency_name'] ?? null)) {
            config(['portal.agency_name' => $settings['agency_name']]);
        }

        if (filled($settings['invite_valid_days'] ?? null)) {
            config(['portal.invite.valid_days' => (int) $settings['invite_valid_days']]);
        }

        if (filled($settings['otp_ttl_minutes'] ?? null)) {
            config(['portal.otp.ttl_minutes' => (int) $settings['otp_ttl_minutes']]);
        }
    }
}
