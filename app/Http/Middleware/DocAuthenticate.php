<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DocAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        $inputpass = request()->input('inputpass', '');
        $password  = config('app.docs_password', '0409');

        if($inputpass === $password)
            return $next($request);
        else
            return view('password', ['result'=>false]);
    }
}
