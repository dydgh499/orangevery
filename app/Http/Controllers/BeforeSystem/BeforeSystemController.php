<?php

namespace App\Http\Controllers\BeforeSystem;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\StoresTrait;
use App\Http\Traits\ExtendResponseTrait;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\BeforeSystem\PaymentGateway;
use App\Http\Controllers\BeforeSystem\PaymentSection;
use App\Http\Controllers\BeforeSystem\Classification;

use App\Http\Controllers\BeforeSystem\Salesforce;
use App\Http\Controllers\BeforeSystem\Merchandise;
use App\Http\Controllers\BeforeSystem\PayModule;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        $response = new StreamedResponse(function () use($brand_id, $before_brand_id) {
            // 클라이언트로 메시지를 보내는 함수
            $result = DB::transaction(function () use($brand_id, $before_brand_id ) {
                $sendClient = function($message, $per) {
                    echo 'data: '.json_encode(['message'=>$message, 'per'=>$per])."\n\n";
                    ob_flush();
                    flush();
                };
                $pg = new PaymentGateway();
                $pg->getPaywell($this->paywell->table('agency_info'), $brand_id, $before_brand_id);
                $pg->setPayvery($this->payvery->table('payment_gateways'), $brand_id);
                $sendClient("PG사 연동을 완료하였습니다.<br>PG사 구간 연동을 시작합니다..", 5);

                $ps = new PaymentSection($pg->payvery, $pg->paywell_to_payvery);
                $ps->getPaywell($this->paywell->table('item_classification'), $brand_id, $before_brand_id);
                $ps->setPayvery($this->payvery->table('payment_sections'), $brand_id);
                $sendClient("PG사 구간 연동을 완료하였습니다.<br>구분 정보 연동을 시작합니다..", 8);

                $cfic = new Classification();
                $cfic->getPaywell($this->paywell->table('item_classification'), $brand_id, $before_brand_id);
                $cfic->setPayvery($this->payvery->table('classifications'), $brand_id);
                $sendClient("구분 정보 연동을 완료하였습니다.<br>영업자 연동을 시작합니다..", 10);

                $sale = new Salesforce();
                $sale->getPaywell($this->paywell, $brand_id, $before_brand_id);
                $sale->setPayvery($this->payvery->table('salesforces'), $brand_id);
                $sendClient("영업자 연동을 완료하였습니다.<br>가맹점 연동을 시작합니다..", 20);

                $mcht = new Merchandise();
                $mcht->connectSalesInfo($sale->payvery, $sale->paywell_to_payvery);
                $mcht->connectClsInfo($cfic->payvery, $cfic->paywell_to_payvery);
                $mcht->getPaywell($this->paywell, $brand_id, $before_brand_id);
                $mcht->setPayvery($this->payvery->table('merchandises'), $brand_id);
                $sendClient("가맹점 연동을 완료하였습니다.<br>결제모듈 연동을 시작합니다..", 40);

                $pmod = new PaymentModule($pg->pg_types);
                $pmod->connectPGInfo($pg->payvery, $pg->paywell_to_payvery, $ps->payvery, $ps->paywell_to_payvery);
                $pmod->connectClsInfo($cfic->payvery, $cfic->paywell_to_payvery);
                $pmod->connectMchtInfo($mcht->payvery, $mcht->paywell_to_payvery);
                $pmod->getPaywell($this->paywell, $brand_id, $before_brand_id);
                $pmod->setPayvery($this->payvery->table('payment_modules'), $brand_id);
                $sendClient("결제모듈 연동을 완료하였습니다.", 60);
                return $this->response(1);
            });
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        return $response;
    }

    public function getPayModule($brand_id)
    {
        
    }

    public function getTerminal($brand_id)
    {
        
    }
}
