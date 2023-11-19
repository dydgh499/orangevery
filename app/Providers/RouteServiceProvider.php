<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

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
        $this->configureRateLimiting();
        $this->routes(function () {
            Route::middleware('api')
            ->prefix('api/v1/bf')
            ->namespace($this->namespace)
                ->group(base_path('routes/bf.php'));

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
            return Limit::perMinute(600)->by(optional($request->user())->id ?: $request->ip())->response(function() use($request) {
                $logs = ['ip'=>request()->ip(), 'method'=>request()->method(),'input'=>request()->all()];
                Log::critical(__('auth.throttle', ["seconds"=> 60]), $logs);
                return response()->json(['message'=>__('auth.throttle', ["seconds"=> 60])], 429);
            });
        });

    }
}
