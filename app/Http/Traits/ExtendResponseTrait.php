<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

trait ExtendResponseTrait 
{
    public function extendResponse($code, $msg)
    {
        $logs = ['ip'=>request()->ip(), 'method'=>request()->method(),'input'=>request()->all()];
        if($code == 990)
        {
            $host = request()->getHost();
            $msg  = ($host != "localhost") ? "Server error" : $msg;   
            $http_code = 500; 
            Log::error($msg, $logs);
        }
        else if($code == 1004)
        {
            $http_code = 409;
            Log::notice($msg, $logs);
        }
        return Response::json(['code'=>$code, 'message'=>$msg], $http_code);        
    }
    public function errorResponse($errors, $http_code=409)
    {
        return Response::json(['errors'=>$errors], $http_code);
    }
    public function clearResponse($data, $http_code=200)
    {
        return Response::json($data, $http_code);
    }
    public function response($code, $data=[])
    {
        switch($code)
        {
            case 0:     $msg = ""; break;
            case 1:     $msg = ""; break;
            case 4:     $msg = ""; break;
            //---------------- route error ----------------- (404)
            case 940:   $msg = "not found route."; break;
            //---------------- auth error ----------------- (950 ~ 959)
            case 950:   $msg = __("auth.failed"); break;
            case 951:   $msg = __("auth.auth"); break;
            case 952:   $msg = __("auth.device"); break;
            case 953:   $msg = "CSRF token mismatch"; break;
            //-------------- server error ----------------- (990 ~ 999)
            case 990:   $msg = "시스템 에러입니다."; break;
            //---------- business logic error ------------- (1000 ~ 1999)
            case 1000:  $msg = __("validation.not_found_obj"); break;
            case 1001:  $msg = __("validation.already_exsit", ['attribute'=>'데이터']); break;
            case 1002:  $msg = __("validation.running_out_point"); break;
            case 1003:  $msg = __("validation.wrong_point_price"); break;
            //user (1100~1199)
            case 1100:  $msg = "패스워드가 틀립니다."; break;
            case 2000:  $msg = "알려지지 않은 에러입니다."; break;
            default:    $msg = "알려지지 않은 코드입니다.";
        }
        
        if($code == 0)
            return Response::json($data, 200);
        else if($code == 1)
            return Response::json($data, 201);
        else if($code == 4)
            return Response::json($data, 204);
        else
        {
            $logs = ['ip'=>request()->ip(), 'method'=>request()->method(),'input'=>request()->all()];
            if($code == 940)
                $http_code = 404;
            else if($code == 950)
                $http_code = 401;
            else if($code == 951 || $code == 952)
                $http_code = 403;                
            else if($code == 953)
                $http_code = 419;
            else if($code > 989 && $code < 1000)
                $http_code = 500;
            else if($code > 999 && $code < 2000)
                $http_code = 409;
            else
                $http_code = 500;
                
            if(($code > 949 && $code < 960) || $code > 999)
                Log::notice($msg, $logs);
            else if($code > 989 && $code < 1000)
                Log::error($msg, $logs);

            return Response::json(['code'=>$code, 'message'=>$msg], $http_code);        
        }
    }
}
