<?php
namespace App\Http\Traits;
use App\Http\Traits\Util\HttpTrait;
use App\Models\Operator;
use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\BankAccount;
use Illuminate\Support\Facades\DB;
use App\Models\Gmid;

trait StoresTrait
{
    use HttpTrait;

    public function getPieces($datas, $peice_size=900)
    {
        $pieces = [];
        $piece = [];
        for($i=0; $i<count($datas); $i++)
        {
            array_push($piece, $datas[$i]);
            if(count($piece) % $peice_size == 0)
            {
                array_push($pieces, $piece);
                $piece = [];
            }
        }
        if(count($piece) > 0)
            array_push($pieces, $piece);
        return $pieces;
    }

    public function manyInsert($orm, $datas)
    {
        $pieces = $this->getPieces($datas);
        return DB::transaction(function () use($orm, $pieces) {
            for($i=0; $i<count($pieces); $i++)
            {
                $res = $orm->insert($pieces[$i]);
                if(!$res)
                    return false;
            }
            return true;
        });
    }

    public function isExistUserName($brand_id, $user_name)
    {
        $checkExist = function($orm, $brand_id, $user_name) {
            return $orm->where('brand_id', $brand_id)
                ->where('is_delete', false)
                ->where('user_name', $user_name)
                ->select('user_name');
        };
        $mcht = $checkExist(new Merchandise, $brand_id, $user_name);
        $sale = $checkExist(new Salesforce, $brand_id, $user_name);
        $oper = $checkExist(new Operator, $brand_id, $user_name);
        $gmid = $checkExist(new Gmid, $brand_id, $user_name);

        return $mcht->unionAll($sale)->unionAll($oper)->unionAll($gmid)->exists();
    }

    public function isExistBulkUserName($brand_id, $user_names)
    {
        $checkExist = function($orm, $brand_id, $user_names) {
            return $orm->where('brand_id', $brand_id)
                    ->where('is_delete', false)
                    ->whereIn('user_name', $user_names)
                    ->select('user_name');
        };
        
        $mcht = $checkExist(new Merchandise, $brand_id, $user_names);
        $sale = $checkExist(new Salesforce, $brand_id, $user_names);
        $oper = $checkExist(new Operator, $brand_id, $user_names);
        $gmid = $checkExist(new Gmid, $brand_id, $user_names);

        return $mcht->unionAll($sale)->unionAll($oper)->unionAll($gmid)->pluck('user_name')->toArray();
    }

    public function isExistMutual($orm, $brand_id, $col, $mutual)
    {
        if($brand_id === 30)
            return false;
        else
        {
            return $orm
            ->where('brand_id', $brand_id)
            ->where('is_delete', false)
            ->where($col, $mutual)
            ->exists();
        }
    }

    public function isExistBulkMutual($orm, $brand_id, $col, $mutuals)
    {
        if($brand_id === 30)
            return [];
        else
        {
            return $orm
            ->where('brand_id', $brand_id)
            ->where('is_delete', false)
            ->whereIn($col, $mutuals)
            ->pluck($col)
            ->toArray();
        }
    }
    
    public function isExistBulkAccountNum($brand_id, $account_nums)
    {
        return BankAccount::where('brand_id', $brand_id)
            ->whereIn('acct_num', $account_nums)
            ->pluck('acct_num')
            ->toArray();
    }
    
    public function ownerCheckForBatch($data)
    {
        $url        = "https://npay.settlebank.co.kr/v1/api/auth/acnt/ownercheck1";
        $mid        = "M2353526";
        $cust_id    = "purple";
        $key        = "ST2305101029388992286";
        $sub_key    = "mWTl0GKBK55eAG3LeMnpNJKJ9oSl5175";

        $params = [
            'hdInfo'        => "SP_NA00_1.0",
            'mchtId'        => $mid,
            'mchtCustId'    => $cust_id,
            'reqDt'         => date('Ymd'),
            'reqTm'         => date('His'),
            'bankCd'        => $data->acct_cd,  // 은행코드
            'custAcntNo'    => $data->acct_num,   // 계좌번호
            'mchtCustNm'    => $data->acct_nm,    // 예금주명
        ];
        $params['pktHash']    = hash("sha256", $params['mchtId'].$params['mchtCustId'].$params['reqDt'].$params['reqTm'].$params['custAcntNo'].$key);
        $params['mchtCustId'] = base64_encode(openssl_encrypt($params['mchtCustId'], "AES-256-ECB",  $sub_key , OPENSSL_RAW_DATA));
        $params['custAcntNo'] = base64_encode(openssl_encrypt($params['custAcntNo'], "AES-256-ECB",  $sub_key , OPENSSL_RAW_DATA));
        $params['mchtCustNm'] = base64_encode(openssl_encrypt($params['mchtCustNm'], "AES-256-ECB",  $sub_key , OPENSSL_RAW_DATA));

        // 실제 API 호출 (post 함수는 적절히 구현되어 있다고 가정)
        $result = $this->post($url, $params, ['Content-Type' => 'application/json']);
        $body = $result['body'];

        // 성공 코드: 0000, ST24
        $cipherRaw  = base64_decode($body['mchtCustNm']);
        $mchtCustNm = openssl_decrypt($cipherRaw, "AES-256-ECB",  $sub_key, OPENSSL_RAW_DATA);
        $success = ($body['outRsltCd'] === "0000" || $body['outRsltCd'] === "ST24");
        $code = $success ? 100 : $body['outRsltCd'];
        $msg  = $success ? $mchtCustNm : $body['outRsltMsg'];

        return [
            'result' => $code,
            'message' => $msg,
            'data' => $body
        ];
    }
}