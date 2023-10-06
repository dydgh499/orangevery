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
use App\Http\Controllers\BeforeSystem\PaymentModule;
use App\Http\Controllers\BeforeSystem\Transaction;

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
        if(preg_match("/^(http[s]?:\/\/)?([^\/\s]+\/)/i", $request->domain, $matches)) 
        {
            $domain = $matches[2];
            $domain = trim($domain, '/');
        } 
        else
            return $this->extendResponse(1000, '도메인을 찾을 수 없습니다.');

        $validated = $request->validate(['domain'=>'required','user_name'=>'required','user_pw'=>'required']);
        $service = $this->paywell->table('service')
            ->where('DNS', $domain)
            ->first();
        if($service)
        {
            $params = [
                'dns_num' => $service->PK,
                'id' => $request->user_name,
                'pw' => $request->user_pw,
            ];
            $res = asPost('https://'.$domain.'/paywell/login/logining.php', $params);
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
        BeforeSystemRegisterJob::dispatch($brand_id, $before_brand_id, $current_brand->dns)
            ->onConnection('redis')
            ->onQueue('computational-transfer');
        sleep(10);
        return $this->extendResponse(1, '전산 이전 작업을 예약하였습니다.<br>5분 내외로 이전 전산에대한 정보가 반영됩니다.');
    }

    public function mchtUpdate()
    {
        $cols = [
            'merchandise.*', 'user.ID', 'user.PW', 
            'user.REP_NM', 'user.SECTORS', 'user.RESIDENT_NUM', 'user.BUSINESS_NUM',
            'user.PHONE', 'user.ADDR', 'user.NICK_NM', 'user.NOTE',
        ];
        $mc = new Merchandise();
        $users = $this->paywell->table('user')
            ->join('merchandise', 'user.PK', '=', 'merchandise.USER_PK')
            ->where('user.DNS_PK', 15)
            ->orderby('user.PK', 'DESC')
            ->get($cols);

        $privacys = $mc->getPaywellPrivacy($this->paywell, $users, 'USER_PK');
        foreach($users as $mcht) 
        {
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
        };
    }

    public function payModUpdate()
    {
        $mchts = $this->paywell->table('user')
            ->join('merchandise', 'user.PK', '=', 'merchandise.USER_PK')
            ->join('terminal', 'user.PK', '=', 'terminal.USER_PK')
            ->where('user.DNS_PK', 15)
            ->where('merchandise.DANGER_DPST_PR', '>', 0)
            ->orderby('user.PK', 'DESC')
            ->get();
            
        foreach($mchts as $mcht) {
            // get the related payment modules
            $paymentModules = $this->payvery->table('payment_modules')
                ->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
                ->where('merchandises.user_name', $mcht->ID)
                ->where('merchandises.brand_id', 2)
                ->select('payment_modules.id as pm_id', 'merchandises.id as m_id')
                ->get();
        
            // iterate through each payment module and update it
            foreach($paymentModules as $paymentModule) {
                $this->payvery->table('payment_modules')
                    ->where('id', $paymentModule->pm_id)
                    ->update(['abnormal_trans_limit' => $mcht->DANGER_DPST_PR]);
                echo $mcht->DANGER_DPST_PR;
            }
        }
    }

    public function getPGs($brand_id, $before_brand_id)
    {
        $afs = $this->payvery->table('payment_gateways')->where('brand_id', $brand_id)->get();
        $bfs = $this->paywell->table('agency_info')->where('DNS_PK', $before_brand_id)->get();
        $afs = json_decode(json_encode($afs), true);
        $bfs = json_decode(json_encode($bfs), true);
        $items = [];
        foreach($afs as $af)
        {
            $idx = array_search($af['pg_name'], array_column($bfs, 'PG_NM'));
            if($idx !== false)
            {
                $key = $bfs[$idx]['PK'];
                $items[$key] = $af['id'];
            }
        }
        return $items;
    }

    public function getPSs($brand_id, $before_brand_id)
    {
        $afs = $this->payvery->table('payment_sections')
            ->where('brand_id', $brand_id)
            ->get();
        $bfs = $this->paywell->table('item_classification')
            ->where('DNS_PK', $before_brand_id)
            ->where('ITEM_TYPE', -1)
            ->get();
        $afs = json_decode(json_encode($afs), true);
        $bfs = json_decode(json_encode($bfs), true);

        $items = [];
        foreach($afs as $af)
        {
            $idx = array_search($af['name'], array_column($bfs, 'ITEM_NM'));
            if($idx !== false)
            {
                $key = $bfs[$idx]['PK'];
                $items[$key] = $af['id'];
            }
        }
        return $items;
    }

    public function getCLs($brand_id, $before_brand_id)
    {
        $afs = $this->payvery->table('payment_sections')
            ->where('brand_id', $brand_id)
            ->get();
        $bfs = $this->paywell->table('item_classification')
            ->where('DNS_PK', $before_brand_id)
            ->where(function ($query) {
                return $query->where('ITEM_TYPE', 0)
                    ->orWhere('ITEM_TYPE', 3);
            })
            ->get();
        $afs = json_decode(json_encode($afs), true);
        $bfs = json_decode(json_encode($bfs), true);

        $items = [];
        foreach($afs as $af)
        {
            $idx = array_search($af['name'], array_column($bfs, 'ITEM_NM'));
            if($idx !== false)
            {
                $key = $bfs[$idx]['PK'];
                $items[$key] = $af['id'];
            }
        }
        return $items;
    }

    public function getMchts($brand_id, $before_brand_id)
    {
        $cols = [
            'merchandise.*', 'user.ID', 'user.PW', 
            'user.REP_NM', 'user.SECTORS', 'user.RESIDENT_NUM', 'user.BUSINESS_NUM',
            'user.PHONE', 'user.ADDR', 'user.NICK_NM', 'user.NOTE',
        ];
        $afs = $this->payvery->table('merchandises')
            ->where('brand_id', $brand_id)
            ->get();
        $bfs = $this->paywell->table('user')
            ->join('merchandise', 'user.PK', '=', 'merchandise.USER_PK')
            ->where('user.DNS_PK', $before_brand_id)
            ->orderby('user.PK', 'DESC')
            ->get(['user.NICK_NM', 'user.PK']);
        $afs = json_decode(json_encode($afs), true);
        $bfs = json_decode(json_encode($bfs), true);

        $items = [];
        foreach($afs as $af)
        {
            $idx = array_search($af['user_name'], array_column($bfs, 'ID'));
            if($idx !== false)
            {
                $key = $bfs[$idx]['PK'];
                $items[$key] = $af['id'];
            }
        }
        return $items;
    }

    public function getSales($brand_id, $before_brand_id)
    {
        $afs = $this->payvery->table('salesforces')
            ->where('brand_id', $brand_id)
            ->get();
        $bfs = $this->paywell->table('user')
            ->whereIn('level', [15,20,30,35])
            ->where('DNS_PK', $before_brand_id)
            ->orderby('PK', 'DESC')
            ->get();
        $afs = json_decode(json_encode($afs), true);
        $bfs = json_decode(json_encode($bfs), true);

        $items = [];
        foreach($afs as $af)
        {
            $idx = array_search($af['user_name'], array_column($bfs, 'ID'));
            if($idx !== false)
            {
                $key = $bfs[$idx]['PK'];
                $items[$key] = $af['id'];
            }
        }
        return $items;
    }

    public function TransactionUpdate()
    {
        $brand_id = 2;
        $before_brand_id = 15;
        $payvery_mods = $this->payvery->table('payment_modules')->where('brand_id', $brand_id);
        $paywell_to_payvery_pgs = $this->getPGs($brand_id, $before_brand_id);
        $paywell_to_payvery_pss = $this->getPSs($brand_id, $before_brand_id);
        $paywell_to_payvery_cls = $this->getCLs($brand_id, $before_brand_id);

        $paywell_to_payvery_mchts = $this->getMchts($brand_id, $before_brand_id);
        $paywell_to_payvery_sales = $this->getSales($brand_id, $before_brand_id);

        $transaction = new Transaction();
        $transaction->connectPGInfo($paywell_to_payvery_pgs, $paywell_to_payvery_pss, $paywell_to_payvery_cls, []);
        $transaction->connectUsers($paywell_to_payvery_mchts, $paywell_to_payvery_sales);
        $transaction->connectPmod($payvery_mods);

        $transaction->getPaywell($this->paywell->table('deposit'), $brand_id, $before_brand_id);
        $transaction->setPayvery($this->payvery->table('transactions'), $brand_id);
    }
}
