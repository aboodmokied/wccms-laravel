<?php

namespace App\Providers;

use App\Models\User;
use App\Services\ClinicalWorkflows\ClinicalWorkflowGatewayInterface;
use App\Services\ClinicalWorkflows\MySqlClinicalWorkflowGateway;
use App\Services\ClinicalWorkflows\OracleClinicalWorkflowGateway;
use App\Services\ClinicalWorkflows\SqliteClinicalWorkflowGateway;
use App\Services\HospitalPatientSyncService;
use App\Services\HospitalPatientSyncServiceInterface;
use App\Support\Hospital;
use App\Support\HospitalStartupCheck;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(HospitalPatientSyncServiceInterface::class, HospitalPatientSyncService::class);
        $this->app->bind(ClinicalWorkflowGatewayInterface::class, function ($app) {
            $driver = $app['db']->connection()->getDriverName();

            if ($app->environment('testing') && $driver === 'sqlite') {
                return new SqliteClinicalWorkflowGateway;
            }

            if ($driver === 'oracle') {
                return new OracleClinicalWorkflowGateway;
            }

            return new MySqlClinicalWorkflowGateway;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app()->setLocale('ar');

        Gate::define('admin-only', function (User $user) {
            return (bool) $user->PER_USER_IS_ACTIVE && $user->PER_USER_ROLE === 'ADMIN';
        });

        Gate::define('clinic-staff', function (User $user) {
            return (bool) $user->PER_USER_IS_ACTIVE && in_array($user->PER_USER_ROLE, ['ADMIN', 'NURSE'], true);
        });

        $this->scheduleHospitalStartupCheck();
    }

    /**
     * Runs the read-only hospital check (App\Support\HospitalStartupCheck) once a day per hospital number, after the
     * first web response of the day has been sent, so it never slows or breaks a request. `php artisan
     * wccms:check-hospital` runs it on demand (e.g. right after a deployment).
     */
    private function scheduleHospitalStartupCheck(): void
    {
        if ($this->app->runningInConsole() || $this->app->runningUnitTests()) {
            return;
        }

        $this->app->terminating(function (): void {
            try {
                if (Cache::add('wccms:hospital-check:'.Hospital::number(), now()->toIso8601String(), now()->addDay())) {
                    $this->app->make(HospitalStartupCheck::class)->run();
                }
            } catch (Throwable $exception) {
                Log::warning('[wccms:check-hospital] لم يكتمل الفحص التلقائي: '.$exception->getMessage());
            }
        });
    }
}
