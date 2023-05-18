<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->url = env('COMAGAIN_BACK_URL', 'http://localhost:8000').'/api/v1/auth';
        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function signIn(Request $request)
    {
        $res = Http::withHeaders($this->headers)->post($this->url.'/sign-in', $request);
        return response($res, $res->status());
    }
    
    public function signOut(Request $request)
    {
        $res = Http::withHeaders($this->headers)->post($this->url.'/sign-out', $request);
        return response($res, $res->status());
    }
    
    public function ok(Request $request)
    {
        $res = Http::withHeaders($this->headers)->post($this->url.'/ok', $request);
        return response($res, $res->status());
    }
}
