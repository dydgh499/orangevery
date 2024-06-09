<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Ablilty\Ablilty;

class LogRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $url = url()->full();
        try
        {
            $logs = [
                'ip'    => $request->ip(),
                'method'=> $request->method(),                
            ];
            $user = $request->user();
            if(isset($user))
            {
                $logs['user_name'] = $user->user_name;
                $logs['brand_id'] = $user->brand_id;
                if(Ablilty::isMerchandise($request))
                    $logs['level'] = 10;
                else if(Ablilty::isSalesforce($request))
                    $logs['level'] = $user->level;
                else if(Ablilty::isOperator($request))
                    $logs['level'] = $user->level;
            }
            $logs['input'] = $request->all();
            Log::info($url, $logs);
        }
        catch(Exception $ex)
        {
            Log::error($url, ['msg'=>$ex->getMessage()]);
        }
        return $next($request);
    }
}
