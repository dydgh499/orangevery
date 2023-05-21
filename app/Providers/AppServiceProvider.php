<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        $this->app->bind(SalesforceController::class, function ($app) {
            return new SalesforceController(
                $app->make(Salesforce::class),
                $app->make(SFFeeChangeHistory::class),
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
    }
}
