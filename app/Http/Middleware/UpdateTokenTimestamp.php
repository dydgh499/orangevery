<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class UpdateTokenTimestamp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        if ($request->user()) {
            $status = $response->getStatusCode();
            if(in_array($status, [200, 201, 204])) {
                $bearer_token = $request->bearerToken();
                if($bearer_token)
                {
                    $access_token = PersonalAccessToken::findToken($bearer_token);
                    if($access_token)
                        PersonalAccessToken::where('id', $access_token->id)->update(['created_at' => now()]);
                }
            }
        }
    }
}
