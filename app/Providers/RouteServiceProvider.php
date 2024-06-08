<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

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
                Route::middleware('api')
                    ->prefix("api/v1/$api")
                    ->namespace($this->namespace)
                        ->group(base_path("routes/$api.php"));
            }
            Route::middleware('api')
                ->prefix('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
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
            return Limit::perMinute(100)->by(optional($request->user())->id ?: $request->ip())->response(function() use($request) {
                Redis::set('blocked:'.$request->ip(), 1, 'EX', (3600*10));
                critical("매크로가 탐지되었습니다.");
                return response('Too Many Requests', 429);
            });
        });
    }
}
