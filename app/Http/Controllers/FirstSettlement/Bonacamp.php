<?php
namespace App\Http\Controllers\FirstSettlement;

use App\Http\Controllers\FirstSettlement\NotiSenderBase;
use App\Http\Controllers\FirstSettlement\FirstSettlementInterface;

class Bonacamp extends NotiSenderBase implements FirstSettlementInterface
{
    static private $headers = [
        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
    ];

    static private function getC3VanId($tran)
    {
        if(isset($tran['temp']))
        {
            $temp = json_decode($tran['temp'], true);
            if($temp && isset($temp['brightfix_c3_van_id']))
                return $temp['brightfix_c3_van_id'];
        }
        return $tran['mid'];
    }

    static public function getParams($tran)
    {
        $trx_dttm = str_replace('-', '', $tran['trx_at']);
        $trx_dttm = str_replace(':', '', $trx_dttm);
        $trx_dttm = str_replace(' ', '', $trx_dttm);

        if(strlen($tran['card_num']) > 12)
        {
            $bin = substr($tran['card_num'], 0, 6);
            $last4 = substr($tran['card_num'], 12, 4);
        }
        else
        {
            $bin = '';
            $last4 = '';
        }

        $van_id = self::getC3VanId($tran);
        $params = [
            'trxId' => $tran['ord_num'],
            'van' => $tran['pg_name'],
            'vanId' => $van_id,
            'tmnId' => $tran['tid'],
            'trxDate' => $trx_dttm,
            'payerName' => $tran['buyer_name'],
            'payerEmail' => '',
            'payerTel' => $tran['buyer_phone'],
            'trackId' => $tran['ord_num'],
            'vanTrxId' => $tran['trx_id'],
            'authCd' => $tran['appr_num'],
            'cardType' => $tran['method'],
            'issuer' => $tran['issuer'],
            'acquirer' => $tran['acquirer'],
            'bin' => $bin,
            'last4' => $last4,
            'installment' => sprintf("%02d", $tran['installment']),
            'amount' => $tran['amount'],
        ];
        if((int)$tran['is_cancel'])
        {
            $params['trxType'] = "refund";
            $params['rootTrxId'] = $tran['ori_trx_id'];

            $mcht = self::getMcht($tran);
            $tran['mchtId'] = $mcht['user_name'];
            $tran['name'] = $mcht['mcht_name'];
        }
        else
        {
            $params['trxType'] = "pay";
            $tran['mchtId'] = $tran['user_name'];
            $tran['name'] = $tran['mcht_name'];
        }
        return [$params, self::$headers];
    }
}

