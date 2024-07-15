<?php
namespace App\Http\Controllers\Ablilty;

use App\Models\Service\AbnormalConnectionHistory;
use App\Http\Controllers\Ablilty\IPInfo;
use App\Http\Controllers\Auth\AuthOperatorIP;
use App\Enums\AbnormalConnectionCode;
use App\Http\Controllers\Manager\Service\BrandInfo;
use Carbon\Carbon;

class AbnormalConnection
{
    static public function create($data)
    {
        $info = IPInfo::get(request());
        $data = array_merge($data, [
            'request_ip' => request()->ip(),
            'request_detail' => $info,
            'mobile_type' => IPInfo::isMobile(request()->ip()),
        ]);
        return AbnormalConnectionHistory::create($data);
    }

    static private function blockIP($min)
    {
        IPInfo::setBlock(request()->ip(), (int)$min*60);
        return Carbon::now()->addMinutes($min)->format('Y-m-d H:i:s');
    }

    static private function getConnectionInfo()
    {
        if(request()->user())
        {
            $brand_id = request()->user()->brand_id;
            $level    = request()->user()->level ? request()->user()->level : 10;
            $user_name = "ID: ".request()->user()->user_name;
        }
        else
        {
            $brand = BrandInfo::getBrandByDNS($_SERVER['HTTP_HOST']);
            $brand_id = $brand['id'];
            $level    = 0;
            $user_name = '';
        }
        return [$brand_id, $level, $user_name];
    }

    static public function privateDataHidden($data)
    {
        if(isset($data['user_pw']))
            unset($data['user_pw']);
        if(strpos(request()->url(), 'v1/manager/services/brands/') !== false)
        {
            if(isset($data['pv_options']))
                unset($data['pv_options']);
            if(isset($data['deposit_day']))
                unset($data['deposit_day']);
            if(isset($data['deposit_amount']))
                unset($data['deposit_amount']);
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        else
            return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /*
        1. 등록되지 않은 IP 운영자계정 로그인시도
            1. 대상 ID
            2. PW: 80% 마스킹처리
    */
    static public function tryNoRegisterIP($user)
    {
        critical('등록되지 않은 IP 운영자계정 로그인시도 ('.request()->ip().")");
        $mask_size = round(strlen(request()->user_pw) * 0.8);
        $pw = substr(request()->user_pw, 0, strlen(request()->user_pw) - $mask_size);
        for ($i=0; $i < $mask_size; $i++) 
        {
            $pw .= '*';
        }
        self::create([
            'brand_id' => $user->brand_id,
            'connection_type' => AbnormalConnectionCode::NO_REGISTER_IP->value,
            'action' => '',
            'target_key' => $user->user_name,
            'target_level' => $user->level,
            'target_value' => $pw,
            'comment' => '',
        ]);
    }

    /*
        2. URL 조작, 파라미터 변조시도
            1. 대상 -> URL
            2. 값 -> 파라미터
    */
    static public function tryParameterModulationApproach()
    {
        critical('URL 조작, 파라미터 변조시도 ('.request()->ip().")");
        $block_time = self::blockIP(60);
        [$brand_id, $level, $user_name] = self::getConnectionInfo();

        self::create([
            'brand_id' => $brand_id,
            'connection_type' => AbnormalConnectionCode::PARAM_MODULATION_APPROCH->value,
            'action' => '1시간 IP차단 (~ '.$block_time.')',
            'target_level'  => $level,
            'target_key'    => request()->url(),
            'target_value'  => self::privateDataHidden(request()->all()),
            'comment' => $user_name,
        ]);
    }

    /*
        3. 작업불가 시간대에 작업시도
            1. 대상 -> URL
            2. 값 -> 파라미터
    */
    static public function tryCannotAllowTime()
    {
        critical('작업불가 시간대에 작업시도 ('.request()->ip().")");
        [$brand_id, $level, $user_name] = self::getConnectionInfo();
        self::create([
            'brand_id' => $brand_id,
            'connection_type' => AbnormalConnectionCode::CANNOT_ALLOW_TIME->value,
            'action' => '',
            'target_level'  => $level,
            'target_key'    => request()->url(),
            'target_value'  => self::privateDataHidden(request()->all()),
            'comment'       => $user_name,
        ]);
    }
    
    /*
        4. 차단된 IP에서 접속시도
            1. 대상 -> 
            2. 값 -> 
    */
    static public function tryBlockIP()
    {
        critical("차단된 IP에서 접속시도 (".request()->ip().")");
        [$brand_id, $level, $user_name] = self::getConnectionInfo();
        $block_time = self::blockIP(60);
        self::create([
            'brand_id' => $brand_id,
            'connection_type' => AbnormalConnectionCode::BLOCK_IP->value,
            'action' => "1시간 IP차단 (~ ".$block_time.")",
            'target_level'  => $level,
            'target_key'    => request()->url(),
            'target_value'  => self::privateDataHidden(request()->all()),
            'comment'       => $user_name,
        ]);
    }
    
    /*
        5. 허용되지 않은 작업 시도 (dev)
            1. 대상 -> url
            2. 값 -> 파라미터
    */
    static public function tryOperationNotPermitted()
    {
        critical("허용되지 않은 작업 시도 (".request()->ip().")");
        $block_time = self::blockIP(60);
        [$brand_id, $level, $user_name] = self::getConnectionInfo();

        self::create([
            'brand_id' => $brand_id,
            'connection_type' => AbnormalConnectionCode::OPERATION_PERMIITED->value,
            'action' => "1시간 IP차단 (~ ".$block_time.")",
            'target_level'  => $level,
            'target_key'    => request()->url(),
            'target_value'  => self::privateDataHidden(request()->all()),
            'comment'       => $user_name,
        ]);
    }

    /*
        6. 매크로 차단
            1. 대상 -> URL
            2. 값 -> PARAMS
    */
    static public function tryMecro()
    {
        [$brand_id, $level, $user_name] = self::getConnectionInfo();
        // 운영자 IP 지역은 제한 제외
        if(AuthOperatorIP::valiate($brand_id, request()->ip()))
            critical('너무많은 접근시도로 잠시 제한');
        else
        {
            critical("매크로 탐지 (".request()->ip().")");
            $block_time = self::blockIP(60);
            self::create([
                'brand_id' => $brand_id,
                'connection_type' => AbnormalConnectionCode::MECRO->value,
                'action' => "1시간 IP차단 (~ ".$block_time.")",
                'target_level'  => $level,
                'target_key'    => request()->url(),
                'target_value'  => self::privateDataHidden(request()->all()),
                'comment'       => '분당 요청수 500회 초과 '.$user_name,
            ]);    
        }
    }

    /*
        8. 해외 IP 접속
            1. 대상 ->
            2. 값 ->
    */
    static public function tryOverseasIP()
    {
        critical('해외 IP 접속시도 ('.request()->ip().")");
        [$brand_id, $level, $user_name] = self::getConnectionInfo();
        $block_time = self::blockIP(10);

        self::create([
            'brand_id' => $brand_id,
            'connection_type' => AbnormalConnectionCode::OVERSEA_IP->value,
            'action' => "10분 IP차단 (~ ".$block_time.")",
            'target_level'  => $level,
            'target_key'    => request()->url(),
            'target_value'  => self::privateDataHidden(request()->all()),
            'comment'       => $user_name,
        ]);
    }

    /*
        8. 세션변경
    */
    static public function notSameLoginIP()
    {
        critical('세션변경 ('.request()->ip().")");
        [$brand_id, $level, $user_name] = self::getConnectionInfo();
        self::create([
            'brand_id' => $brand_id,
            'connection_type' => AbnormalConnectionCode::NOT_SAME_LOGIN_IP->value,
            'action'        => "로그아웃",
            'target_level'  => $level,
            'target_key'    => request()->url(),
            'target_value'  => self::privateDataHidden(request()->all()),
            'comment'       =>  $user_name.'('.request()->user()->last_login_ip.' -> '.request()->ip().')',
        ]);
    }

    /*
        9. 브라우저가 아님
    */
    static public function notBrowser()
    {
        critical('user-agent 없음 ('.request()->ip().")");
        [$brand_id, $level, $user_name] = self::getConnectionInfo();
        $block_time = self::blockIP(60);

        self::create([
            'brand_id' => $brand_id,
            'connection_type' => AbnormalConnectionCode::NOT_BROWSER->value,
            'action'        => "1시간 IP차단 (~ ".$block_time.")",
            'target_level'  => $level,
            'target_key'    => request()->url(),
            'target_value'  => self::privateDataHidden(array_merge(request()->all(), request()->headers->all())),
            'comment'       => $user_name,
        ]);
    }
}
