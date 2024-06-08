<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DocAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        $inputpass = request()->input('inputpass', '');

        print_r($request->ips());
        echo request()->path();
        if($request->is('log-viewer/*'))
            $password = 'masterpurple1324@';
        else
            $password = config('app.docs_password', '0409');

        if($inputpass === $password)
            return $next($request);
        else
        {
            $uri = request()->path();
            return redirect('password?uri='.$uri);
        }
    }
}
