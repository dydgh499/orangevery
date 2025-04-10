<?php

namespace App\Providers;

use App\Http\Controllers\Ablilty\ActivityHistoryInterface;
use App\Models\Log\ActivityHistory;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Doctrine\DBAL\Types\Type;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ActivityHistoryInterface::class, function ($app) {
            return new ActivityHistoryInterface(new ActivityHistory());
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->environment('local')) {
            DB::listen(function ($query) {
                Log::info(
                    $query->sql,
                    $query->bindings,
                    $query->time
                );
            });
        }
        if (!Type::hasType('tinyinteger'))
            Type::addType('tinyinteger', \Doctrine\DBAL\Types\SmallIntType::class);
        Paginator::useBootstrap();
    }
}
