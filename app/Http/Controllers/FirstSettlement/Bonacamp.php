<?php
namespace App\Http\Controllers\FirstSettlement;

use App\Http\Controllers\FirstSettlement\FirstSettlementBase;
use App\Http\Controllers\FirstSettlement\FirstSettlementInterface;

class Bonacamp extends FirstSettlementBase implements FirstSettlementInterface
{
    static private $headers = [
        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
    ];

    static public function getParams($tran)
    {
        $trx_dttm = str_replace('-', '', $tran['trx_at']);
        $trx_dttm = str_replace(':', '', $trx_dttm);

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

        $params = [
            'trxId' => $tran['ord_num'],
            'van' => $tran['pg_name'],
            'vanId' => $tran['mid'],
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

            $mcht = self::getMcht($tran['mcht_id']);
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

