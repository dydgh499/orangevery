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
    
    public function __construct($type="")
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
        $res = Http::withHeaders($this->headers)->get($this->url."/$id", $request);
        return response($res, $res->status());
    }

    public function update(Request $request, $id)
    {
        $res = Http::withHeaders($this->headers)->put($this->url."/$id", $request);
        return response($res, $res->status());
    }

    public function destroy(Request $request, $id)
    {
        $res = Http::withHeaders($this->headers)->delete($this->url."/$id", $request);
        return response($res, $res->status());
    }

    public function domain(Request $request)
    {
        $url    = env('COMAGAIN_BACK_URL', 'http://localhost:8000');
        $host   = env('APP_ENV') == "local" ? "localhost" : $_SERVER['HTTP_HOST'];
        $res    = Http::get("$url/api/v1/auth/domain?dns=$host");
        if($res->status() == 200)
        {
            $json = $res->json();
            $cookies = $res->cookies()->toArray();
            $com_csrf_token = '';
            for($i=0; $i<count($cookies); $i++)
            {
                if($cookies[$i]['Name'] === 'COM-XSRF-TOKEN')
                {
                    $com_csrf_token = $cookies[$i]['Value'];
                    break;
                }
            }
            $json['css']   = json_decode($json['theme_css'], true);
            $json['color'] = isset($json['css']['main_color']) ? $json['css']['main_color'] : "#7367F0";
            $json['com_csrf_token'] = $com_csrf_token;
            return response(view('application', ['json' => $json]))
                ->withCookie('XSRF-TOKEN', $json['com_csrf_token']);
        }
    }
}
