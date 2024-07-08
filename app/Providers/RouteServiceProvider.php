<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ablilty\AbnormalConnection;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $apis = ['bf', 'buddy-pay', 'ezpg'];
        $this->configureRateLimiting();
        $this->routes(function () use($apis) {
            foreach($apis as $api) {
                Route::middleware(['api', 'log.route'])
                    ->prefix("api/v1/$api")
                    ->namespace($this->namespace)
                        ->group(base_path("routes/$api.php"));
            }

            Route::middleware(['api', 'log.route', 'auth:sanctum', 'is.browser'])
                ->prefix('api/v1/manager')
                ->namespace($this->namespace)
                ->group(base_path('routes/api-service.php'));

            Route::middleware(['api', 'log.route', 'auth:sanctum', 'is.browser'])
                ->prefix('api/v1/manager')
                ->namespace($this->namespace)
                ->group(base_path('routes/api-transaction.php'));

            Route::middleware(['api', 'log.route', 'auth:sanctum', 'is.browser'])
                ->prefix('api/v1/manager')
                ->namespace($this->namespace)
                ->group(base_path('routes/api-user.php'));

            Route::middleware(['api', 'is.browser'])
                ->prefix('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware(['web', 'is.browser'])
            ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(300)->by(optional($request->user())->id ?: $request->ip())->response(function() use($request) {
                AbnormalConnection::tryMecro();
                return response('Too Many Requests', 429);
            });
        });
    }
}
