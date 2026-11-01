<?php

namespace App\Providers;

use App\Models\User;
use App\Services\ClinicalWorkflows\ClinicalWorkflowGatewayInterface;
use App\Services\ClinicalWorkflows\MySqlClinicalWorkflowGateway;
use App\Services\ClinicalWorkflows\OracleClinicalWorkflowGateway;
use App\Services\ClinicalWorkflows\SqliteClinicalWorkflowGateway;
use App\Services\HospitalPatientSyncService;
use App\Services\HospitalPatientSyncServiceInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
    }
}
