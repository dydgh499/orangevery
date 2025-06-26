<?php

namespace App\Jobs\Realtime\Coocon;
use App\Jobs\Realtime\Coocon\Coocon;
use Illuminate\Support\Str;
use App\Http\Controllers\Utils\Comm;

class CooconWrap extends Coocon
{
    public function __construct($finance_van, $privacy, $deposit_type, $withdraw_book_time)
    {
        parent::__construct($finance_van, $privacy, $deposit_type, $withdraw_book_time);
        if(isset($this->privacy['acct_bank_name']))
        {
            $this->privacy['acct_bank_code'] = $this->getBankCode($this->privacy['acct_bank_name']);
            $this->privacy['acct_num']  = str_replace('-', '', $this->privacy['acct_num']);
            $this->privacy['acct_num']  = str_replace(' ', '', $this->privacy['acct_num']);
        }
    }

    public function getBalance()
    {
        $params = $this->getAuthParams();
        $params["KEY"] = "6140";
        return $this->send($params, false);
    }

    public function deposit()
    {
        $params = array_merge($this->getAuthParams(), $this->getRecvParams());
        $params["KEY"] = "6120";
        $params["WDRW_ACCT_NO"]  = $this->finance_van['withdraw_acct_num'];
        $params["WDRW_ACCT_NM"]  = mb_substr($this->getDepositName(), 0, 10);
        $res = $this->send($params);

        if($res['RESP_CD'] === '0000')
            return $this->getDepositResult($this->trsc_no);
        else
            return $res;
    }

    public function getDepositResult($ori_trx_id)
    {
        $params = $this->getAuthParams();
        $params["KEY"] = "6170";
        $params["RQRE_TMSG_NO"] = $ori_trx_id;
        $params["REQ_TRSC_DT"]  = date("Ymd");
        return $this->send($params);
    }

    public function oAuthTokenPublish()
    {
        return Comm::post($this->auth_host."/oauth/2.0/token", [
            'grant_type'    => 'client_credentials',
            'scope'         => 'apis',
            'client_id'     => $this->finance_van['corp_code'],
            'client_secret' => $this->finance_van['api_key'],
        ], [
            "Content-Type"  => "application/x-www-form-urlencoded",
        ]);
    }

    public function oAuthTokenRevoke($access_token)
    {
        return Comm::post($this->auth_host."/oauth/2.0/revoke", [
            'access_token'      => $access_token,
            'token_type_hint'   => 'access_token',
            'client_id'     => $this->finance_van['corp_code'],
            'client_secret' => $this->finance_van['api_key'],
        ], [
            "Content-Type"  => "application/x-www-form-urlencoded",
        ]);
    }

    public function kakaoAuthRequest($phone_num, $name, $birthday, $product_type, $return_url='')
    {
        $code       = 0;
        $message    = "";
        $data       = [];
        $res = $this->oAuthTokenPublish();
        if($res['code'] === 200)
        {
            $access_token = $res['body']['access_token'];
            $res = Comm::post($this->auth_host."/v2/certification/kakao/sign/request/$product_type", [
                'type'      => 'PERSONAL_INFO',
                'isCd'      => $this->finance_van['corp_code'],
                'tranId'    => $this->trsc_no,
                'phoneNo'   => $phone_num,
                'name'      => $name,
                'birthday'  => $birthday,
                'expiresIn' => 1200,
                'data'      => Str::random(40),
                'delegateInfo'  => '본인인증 요청',
                'extraMessage' => $name."님! 안녕하세요. 서비스 이용을 위한 본인인증을 요청합니다.",
                'returnUrl'    => $return_url ? $return_url : '',
                'receiverName' => $name
            ], [
                'Authorization' => "Bearer ".$access_token,
                "Content-Type"  => "application/x-www-form-urlencoded",
            ]);
            if($res['code'] === 200)
            {
                if($res['body']['result'] === "Y")
                {
                    $data = [
                        'tx_id'     => $tx_id,
                        'scheme'    => $product_type === 'K1120' ? $res['body']['scheme'] : '',
                    ];
                }
                else
                {
                    $code       = 1999;
                    $message    = '인증접수를 실패하였습니다.';
                }
            }
            else
                [$code, $message] = $this->getAuthErrorInfo($res, '인증요청을 실패하였습니다.');

            $this->oAuthTokenRevoke($access_token);
        }
        else
            [$code, $message] = $this->getAuthErrorInfo($res, '인증토큰 발급을 실패하였습니다.');

        return [
            'code'      => $code,
            'message'   => $message,
            'data'      => $data
        ];
    }

    public function kakaoAuthVertify($tx_id)
    {
        $code       = 0;
        $message    = "";
        $data       = [];
        $res = $this->oAuthTokenPublish();
        if($res['code'] === 200)
        {
            $access_token = $res['body']['access_token'];
            $headers = [
                'Authorization' => "Bearer ".$access_token,
                "Content-Type"  => "application/x-www-form-urlencoded",
            ];
            $res = Comm::post($this->auth_host."/v2/certification/kakao/sign/request/status", [
                'isCd'  => $this->finance_van['corp_code'],
                'tranId'=> $this->trsc_no,
                'txId'  => $tx_id
            ], $headers);
            if($res['code'] === 200)
            {
                if($res['body']['status'] === 'REQUESTED')
                {
                    $code       = 1999;
                    $message    = '아직 전자서명을 완료하지 않았습니다.';
                }
                else if($$res['body']['status'] === 'EXPIRED')
                {
                    $code       = 1999;
                    $message    = '요청이 만료되었습니다.';
                }
                else
                {
                    $res = Comm::post($this->auth_host."/v2/certification/kakao/sign/request/verify", [
                        'isCd'  => $this->finance_van['corp_code'],
                        'tranId'=> $this->trsc_no,
                        'txId'  => $tx_id
                    ], $headers);
                    if($res['code'] === 200)
                    {
                        if($res['body']['result'] === 'N')
                        {
                            $code       = 1999;
                            $message    = '검증요청을 실패하였습니다.';
                        }
                    }
                    else
                        [$code, $message] = $this->getAuthErrorInfo($res, '인증검증을 실패하였습니다.');
                }
            }
            else
                [$code, $message] = $this->getAuthErrorInfo($res, '인증조회를 실패하였습니다.');

            $this->oAuthTokenRevoke($access_token);
        }
        else
            [$code, $message] = $this->getAuthErrorInfo($res, '인증토큰 발급을 실패하였습니다.');

        return [
            'code'      => $code,
            'message'   => $message,
            'data'      => $data
        ];
    }



    protected function getAccountAuthCommonParams($trx_at)
    {
        return [
            'ID'        => $this->finance_van['corp_code'],
            'RQ_DTIME'  => $trx_at,
            'TNO'       => $this->trsc_no,
            'EM'        => 'AES',          // 알고리즘 종류
            'VM'        => 'HmacSHA256',   // MAC 생성 알고리즘
        ];
    }

    public function accountAuthRequest($trx_at, $rand_name)
    {
        $host       = 'https://'.($this->is_test ? 'dev' : 'www').'.checkpay.co.kr';
        $code       = 0;
        $message    = "";
        $data       = [];
        if($this->privacy['acct_bank_name'] === null)
        {
            $code       = 1999;
            $message    = '지원하지 않는 은행입니다.';
        }
        else
        {
            $body   = json_encode([
                'fnni_cd'   => $this->privacy['acct_bank_code'],
                'acct_no'   => $this->privacy['acct_num'],
                'memb_nm'   => $this->privacy['acct_name'],
                'verify_tp' => "T",
            ], JSON_UNESCAPED_UNICODE);

            $trx_at         = $this->getTrxDttm($trx_at);
            $params         = $this->getAccountAuthCommonParams($trx_at);
            $params['EV']   = $this->getAccountAuthAESEncrypt($trx_at.$body);
            $params['W']    = $this->getAccountAuthHmacSha256($body);
            $res = Comm::post($host."/CPIF_AFFL_720.jct", $params, ['Content-Type' => 'application/x-www-form-urlencoded"']);
            if($res['code'] === 200)
            {
                if($res['data']['RC'] === '0000')
                {
                    $rev = $this->getAccountAuthAESDecrypt($res['data']['EV']);
                    if($this->verifyAccountAuthHmac($res['data']['EV'], $res['data']['VV']))
                    {
                        $code       = 1999;
                        $message    = '암호화 검증에러';
                    }
                    else
                    {
                        $dec_ev = substr($rev, 14);
                        $obj    = json_decode($dec_ev, true);
                        $this->trsc_no = $obj['verify_tr_no'];
                    }
                }
                else
                {
                    $code       = $res['data']['RC'];
                    $message    = $res['data']['RM'];
                }
            }
            else
            {
                $code       = isset($res['data']['RC']) ? $res['data']['RC'] : 1999;
                $message    = isset($res['data']['RM']) ? $res['data']['RM'] : '타임아웃';
            }
        }

        return [
            'code'      => $code,
            'message'   => $message,
            'data'      => $data
        ];
    }

    public function accountAuthVertify($ori_trx_id, $ori_trx_at, $depositer_name='')
    {
        $host   = 'https://'.($this->is_test ? 'dev' : 'www').'.checkpay.co.kr';
        $ori_trx_at = $this->getTrxDttm($ori_trx_at);
        $params = $this->getAccountAuthCommonParams($ori_trx_at);
        $params['verify_tr_no'] = $ori_trx_id;
        $params['verify_val']   = $depositer_name;
        $res = Comm::post($host."/CPIF_AFFL_721.jct", $params, ['Content-Type' => 'application/x-www-form-urlencoded"']);
        if($res['code'] === 200)
        {
            if($res['data']['RC'] === '0000')
            {
                $rev = $this->getAccountAuthAESDecrypt($res['data']['EV']);
                if($this->verifyAccountAuthHmac($res['data']['EV'], $res['data']['VV']))
                {
                    $code       = 1999;
                    $message    = '암호화 검증에러';
                }
                else
                {
                    $dec_ev = substr($rev, 14);
                    $obj    = json_decode($dec_ev, true);
                    $data   = ['depositer_name' => $obj['verify_val']];
                }
            }
            else
            {
                $code       = isset($res['data']['RC']) ? $res['data']['RC'] : 1999;
                $message    = isset($res['data']['RM']) ? $res['data']['RM'] : '타임아웃';
            }
        }
        else
        {
            $code       = isset($res['data']['RC']) ? $res['data']['RC'] : 1999;
            $message    = isset($res['data']['RM']) ? $res['data']['RM'] : '타임아웃';
        }

        return [
            'code'      => $code,
            'message'   => $message,
            'data'      => $data
        ];
    }
}
