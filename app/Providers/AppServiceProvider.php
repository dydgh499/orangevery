<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(SalesforceController::class, Salesforce::class);
        $this->app->bind(PaymentModuleController::class, PaymentModule::class);
        $this->app->bind(PaymentGatewayController::class, function ($app) {
            return new PaymentGatewayController(
                $app->make(PaymentGateway::class),
                $app->make(PaymentSection::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
       # if ($this->app->environment('local')) {
            DB::listen(function ($query) {
                Log::info(
                    $query->sql,
                    $query->bindings,
                    $query->time
                );
            });
       # }
        Paginator::useBootstrap();
    }
}
