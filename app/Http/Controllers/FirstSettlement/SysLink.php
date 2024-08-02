<?php
namespace App\Http\Controllers\FirstSettlement;

use App\Http\Controllers\FirstSettlement\NotiSenderBase;
use App\Http\Controllers\FirstSettlement\FirstSettlementInterface;
use App\Http\Controllers\Utils\Comm;

class SysLink extends NotiSenderBase implements FirstSettlementInterface
{
    static private $host = "https://dapi.syslink.kr";
    static private $headers = [
        'Content-Type' => 'application/json; charset=utf-8',
        'Authorization' => 'sk_de0c1dbee096ee9a54d9341cd60fc',
    ];

    static public function create($mcht)
    {
        $params = [
            "id"          => $mcht['user_name'],
            "status"      => "active",
            "brnType"     => "사업자",
            "brn"         => $mcht['business_num'],
            "nick"        => $mcht['mcht_name'],
            "name"        => $mcht['nick_name'],
            "bizType"     => $mcht['sector'],
            "phoneNo"     => $mcht['phone_num'],
            "telNo"       => $mcht['contact_num'],
            "email"       => "",
            "address"     => $mcht['addr'],
            "accnt" => [
                "bankCd"    => $mcht['acct_bank_code'],
                "account"   => $mcht['acct_num'],
                "holder"    => $mcht['acct_name']
            ]
        ];
        $res = Comm::post(self::$host.'/v1/sapp/create', $params, self::$headers);
        return $res['body'];
    }

    static public function update($mcht)
    {
        $params = [
            "status"      => "active",
            "brnType"     => "사업자",
            "brn"         => $mcht['business_num'],
            "nick"        => $mcht['mcht_name'],
            "name"        => $mcht['nick_name'],
            "bizType"     => $mcht['sector'],
            "phoneNo"     => $mcht['phone_num'],
            "telNo"       => $mcht['contact_num'],
            "email"       => "",
            "address"     => $mcht['addr'],
        ];
        $res = Comm::post(self::$host.'/v1/sapp/update/'.$mcht['user_name'], $params, self::$headers);

        $acct_params = [
            "bankCd"    => $mcht['acct_bank_code'],
            "account"   => $mcht['acct_num'],
            "holder"    => $mcht['acct_name']
        ];
        $res = Comm::post(self::$host.'/v1/accnt/update/'.$mcht['user_name'], $acct_params, self::$headers);
        return $res['body'];
    }

    static public function show($user_name)
    {
        $res = Comm::get(self::$host.'/v1/sapp/retrieve/'.$user_name, [], self::$headers);
        return $res['body'];
    }

    static public function getParams($tran)
    {
        $root_trx_dt = str_replace('-', '', $tran['trx_dt']);
        $module_type = $tran['module_type'] === 0 ? 'OFFLINE' : 'ONLINE';

        if((int)$tran['is_cancel'])
        {
            $mcht = self::getMcht($tran);
            $user_name = $mcht['user_name'];
            $ori_trx_id = $tran['ori_trx_id'];
            $trx_type = '취소';
            $trx_dt = str_replace('-', '', $tran['cxl_dt']);
        }
        else
        {
            $user_name = $tran['user_name'];
            $ori_trx_id = $tran['trx_id'];
            $trx_type = '승인';
            $trx_dt = str_replace('-', '', $tran['trx_dt']);
        }
        $params = [
            "sappId"   => $user_name,
            "amount"   => $tran['mcht_settle_amount'],
            "trackId"  => $tran['ord_num'],
            "reserve1" => $tran['id'],
            "reserve2" => "",
            "trxs" => [
                [
                    "trackId"       => $tran['ord_num'],
                    "orgTrxId"      => $tran['trx_id'],
                    "method"        => "CARD",
                    "route"         => $module_type,
                    "trxType"       => $trx_type,
                    "number"        => $tran['card_num'],
                    "unitType"      => "신용",
                    "amount"        => $tran['amount'],
                    "authCd"        => $tran['appr_num'],
                    "installment"   => sprintf("%02d", $tran['installment']),
                    "pdtName"       => $tran['item_name'],
                    "custName"      => $tran['buyer_name'],
                    "trxDay"        => $trx_dt,
                    "rootDay"       => $root_trx_dt,
                    "rootOrgTrxId"  => $ori_trx_id,
                ]
            ]
        ];
        return [$params, self::$headers];
    }
}
