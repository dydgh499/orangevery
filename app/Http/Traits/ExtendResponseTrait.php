<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Response;

trait ExtendResponseTrait 
{
    public function extendResponse($code, $msg)
    {
        if($code == 990)
        {
            $host = request()->getHost();
            $msg  = ($host != "localhost") ? "Server error" : $msg;   
            $http_code = 500; 
        }
        else if($code == 1004)
        {
            $http_code = 409;
        }
        return Response::json(['code'=>$code, 'message'=>$msg], $http_code);        
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
            case 950:   $msg = "Authentication token is missing or incorrect"; break;
            case 951:   $msg = "You do not have permission"; break;
            case 952:   $msg = "This is an unapproved device"; break;
            case 953:   $msg = "CSRF token mismatch"; break;
            //-------------- server error ----------------- (990 ~ 999)
            case 990:   $msg = "시스템 에러입니다."; break;
            //---------- business logic error ------------- (1000 ~ 1999)
            case 1000:  $msg = "데이터를 찾을 수 없습니다."; break;
            case 1001:  $msg = "이미 존재합니다."; break;
            case 1002:  $msg = "포인트가 부족합니다."; break;
            case 1003:  $msg = "포인트 금액이 이상합니다."; break;
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
            return Response::json(['code'=>$code, 'message'=>$msg], $http_code);        
        }
    }
}
