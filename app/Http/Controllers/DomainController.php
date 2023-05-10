<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class DomainController extends Controller
{
    public function __invoke()
    {
        $url = env('COMAGAIN_BACK_URL', 'http://localhost:8000');
        $res = Http::get("$url/api/v1/auth/domain?dns=".$_SERVER['HTTP_HOST']);
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
