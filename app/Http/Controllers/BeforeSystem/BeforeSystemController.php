<?php

namespace App\Http\Controllers\BeforeSystem;

use App\Jobs\BeforeSystemRegisterJob;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\StoresTrait;
use App\Http\Traits\ExtendResponseTrait;

use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\BeforeSystem\Merchandise;

class BeforeSystemController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $paywell, $payvery;

    public function __construct()
    {
        $this->paywell = DB::connection('paywell');
        $this->payvery = DB::connection('mysql');
    }

    public function login(Request $request)
    {
        $validated = $request->validate(['domain'=>'required','user_name'=>'required','user_pw'=>'required']);
        $service = $this->paywell->table('service')
            ->where('DNS', $request->domain)
            ->first();
        if($service)
        {
            $params = [
                'dns_num' => $service->PK,
                'id' => $request->user_name,
                'pw' => $request->user_pw,
            ];
            $res = asPost('https://'.$request->domain.'/paywell/login/logining.php', $params);
            if($res['body']['result'] != 100)
                return $this->extendResponse(2000, $res['body']['message']);
            else
                return $this->response(0, $res['body']['data']);
        }
        else
            return $this->extendResponse(1000, '도메인을 찾을 수 없습니다.');
    }

    public function register(Request $request)
    {
        $validated = $request->validate(['token'=>'required']);
        $dehashing = function($token) {
            $parted = explode('%', base64_decode($token));      // 토큰 만들때의 구분자 . 으로 나누기
            if(count($parted) > 1) {
                $signature = $parted[2];
                // 위에서 토큰 만들때와 같은 방식으로 시그니처 만들고 비교
                if(hash('sha256', $parted[0].$parted[1]) != $signature)
                    return [];
                $payload = json_decode($parted[1], true);
                return $payload;    
            }
            else
                return [];
        };
        $user = $dehashing($request->token);
        $before_brand_id = $user['data']['DNS_PK'];
        $brand_id = $request->brand_id;

        $current_brand = $this->payvery->table('brands')->where('id', $brand_id)->first();
        BeforeSystemRegisterJob::dispatch($brand_id, $before_brand_id, $current_brand->dns);
        #    ->onConnection('redis')
        #    ->onQueue('computational-transfer');
        return $this->extendResponse(1, '전산 이전 작업을 예약하였습니다.<br>5분 내외로 이전 전산에대한 정보가 반영됩니다.');
    }

    function mchtUpdate()
    {
        $mc = new Merchandise();
        $mchts = $this->paywell->table('user')
            ->join('merchandise', 'user.PK', '=', 'merchandise.USER_PK')
            ->where('user.DNS_PK', 15)
            ->orderby('user.PK', 'DESC')
            ->get();
            
        $privacys = $mc->getPaywellPrivacy($this->paywell, $mchts, 'USER_PK');
        logging(json_decode(json_encode($privacys), true));
        foreach($mchts as $mcht) {
            $privacy = $privacys->first(function($item) use ($mcht) {
                return $item->USER_PK == $mcht->USER_PK;
            });
            if($privacy)
            {
                $update = [
                    'acct_num'  => $privacy ? $privacy->ACCT_NUM : null,
                    'acct_name'  => $privacy ? $privacy->ACCT_NM : null,
                    'acct_bank_name'  => $privacy ? $privacy->ACCT_BANK : null,
                    'acct_bank_code'  => $privacy ? sprintf("%03d", (int)$privacy->ACCT_BANK_CD) : null,
                ];
                $res = $this->payvery->table('merchandises')
                    ->where('brand_id', 2)
                    ->where('user_name', $mcht->ID)
                    ->update($update);
                echo $res;    
            }
            else
                echo $mcht->USER_PK;
            echo "\n";
        };
    }
}
