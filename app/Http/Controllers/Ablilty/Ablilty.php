<?php
namespace App\Http\Controllers\Ablilty;

use App\Http\Controllers\Ablilty\AbnormalConnection;

class Ablilty
{
    static function isOperator($request)
    {
        return $request->user()->tokenCan(35);
    }

    static function isMyOperator($request, int $id)
    {
        return self::isOperator($request) && $request->user()->id === $id;
    }

    static function isEmployee($request)
    {
        return self::isOperator($request) && $request->user()->tokenCan(40) === false;
    }

    static function isDevLogin($request)
    {
        return $request->user()->tokenCan(50) && self::isDevOffice($request);
    }
    
    static function isAppLocal()
    {
        return env('APP_ENV') === 'local';
    }

    static function isAppStage()
    {
        return env('APP_ENV') === 'stage';
    }

    static function isDevOffice($request)
    {
        $ips = json_decode(env('MASTER_IPS'), true) ?? [];
        if(env('APP_ENV') === 'local')
            array_push($ips, '127.0.0.1');
        return in_array($request->ip(), $ips);
    }

    static function isDeliveryAgencyServer($request)
    {
        $ips = json_decode(env('DELIVERY_AGENCY_IPS'), true) ?? [];
        if(env('APP_ENV') === 'local')
            array_push($ips, '127.0.0.1');
        return in_array($request->ip(), $ips);
    }

    static function isBrandCheck($request, $brand_id, $is_dev_ok=false)
    {
        if($is_dev_ok)
        {
            $cond_1 = self::isDevLogin($request);
            $cond_2 = ($request->user()->brand_id !== $brand_id);
            if($cond_1 === false && $cond_2)
            {
                AbnormalConnection::tryParameterModulationApproach();
                return false;
            }
            else
                return true;
        }
        else
        {
            if($request->user()->brand_id !== $brand_id)
            {
                AbnormalConnection::tryParameterModulationApproach();
                return false;
            }
            else
                return true;
        }
    }

    static public function emptyPrivacyInfo($request, $data)
    {
        if(self::isOperator($request) === false)
        {
            unset($data['user_name']);
            unset($data['nick_name']);
            unset($data['phone_num']);
            unset($data['addr']);
            unset($data['business_num']);
            unset($data['resident_num']);
            unset($data['acct_num']);
            unset($data['acct_name']);
            unset($data['acct_bank_code']);
            unset($data['acct_bank_name']);    
        }
        return $data;
    }
}
