<?php

namespace App\Http\Controllers;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ComagainController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    
    public function __construct($type)
    {
        $this->url = env('COMAGAIN_BACK_URL', 'http://localhost:8000').'/api/v1/manager/'.$type;
        $this->headers  = [
            'Authorization' => "Bearer ".request()->bearerToken(),
            'Content-Type'  => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function index(Request $request)
    {
        $res = Http::withHeaders($this->headers)->get($this->url, $request);
        return response($res, $res->status());
    }

    public function store(Request $request)
    {
        $res = Http::withHeaders($this->headers)->post($this->url, $request);
        return response($res, $res->status());
    }

    public function show(Request $request, $id)
    {
        $res = Http::withHeaders($this->headers)->get($this->url+"/$id", $request);
        return response($res, $res->status());
    }

    public function update(Request $request, $id)
    {
        $res = Http::withHeaders($this->headers)->put($this->url+"/$id", $request);
        return response($res, $res->status());
    }

    public function destroy(Request $request, $id)
    {
        $res = Http::withHeaders($this->headers)->delete($this->url+"/$id", $request);
        return response($res, $res->status());
    }
}
