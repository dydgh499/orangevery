<?php
namespace App\Http\Controllers\Option;

use App\Http\Controllers\Option\PaymentTimeValidate;
use App\Http\Controllers\Option\HandPayValidate;
use App\Models\Merchandise\BillKey;
use Carbon\Carbon;

class BillPayValidate extends HandPayValidate
{
    // 빌키 조회
    static public function getBillKey($mid, $tid, $pmod_id, $bill_key)
    {
        $mid = $mid === null ? "" : $mid;
        $tid = $tid === null ? "" : $tid;
        $pmod_id = $pmod_id === null ? "" : $pmod_id;
        $bill_key = $bill_key === null ? "" : $bill_key;
        $pay_key = request()->header('Authorization', request()->input('pay_key', ''));

        $query = BillKey::join('payment_modules', 'bill_keys.pmod_id', '=', 'payment_modules.id')
                    ->where('payment_modules.is_delete', false);

        if($mid === "" && $tid === "" && $pmod_id === "" && $pay_key === "")
            return null;
        else
        {
            if($mid !== '')
                $query = $query->where('payment_modules.mid', $mid);
            /*if($tid !== '')
                $query = $query->where('payment_modules.tid', $tid);*/
            if($pay_key !== '')
                $query = $query->where('payment_modules.pay_key', $pay_key);
            if($pmod_id !== '')
                $query = $query->where('payment_modules.id', $pmod_id);
            if($bill_key !== '')
                $query = $query->where('bill_keys.bill_key', $bill_key);
            return $query->first();
        }
    }

    static public function getBillCreateFormat($pmod, $request)
    {
        $data = [
            'nick_name' => $request->input('nick_name', ''),
            'mcht_id'   => $pmod->mid,
            'tid'       => $pmod->tid,
            'pg_id'     => $pmod->pg_id,
            'ord_num'   => $request->ord_num,
            'buyer_name' => $request->input('buyer_name', ''),
            'buyer_phone' => $request->input('buyer_phone', ''),
            'card_num'  => $request->card_num,
            'yymm'      => $request->yymm,
            'auth_num'  => $request->auth_num,
            'card_pw'   => $request->card_pw,
            'amount'    => 0,   // fail trans
        ];
        return $data;
    }

    static public function getBillDeleteFormat($pmod, $request)
    {
        return  [
            'mcht_id'   => $pmod->mid,
            'ord_num'   => $request->ord_num,
            'bill_key'  => $request->bill_key,
            'amount'    => 0,   // fail trans
        ];
    }

    static public function getBillPayFormat($pmod, $request, $bill_key)
    {
        $data = parent::getPayFormat($pmod, $request);
        $data['auth_num'] = '0000'; //임시 값
        $data['card_pw'] = '00';
        $data['card_num'] = $bill_key->card_num;
        return $data;
    }

    // 빌키발급 검증
    static public function createValidate($db, $pmod, $noti)
    {
        [$code, $message] = ['0000', ''];
        $pg_name = getPGType($pmod->pg_type);

        if(self::merchantStatusValidate($pmod) === false)
            [$code, $message] = ['PV412', '사용불가 가맹점 입니다.', $pg_name];
        else if(self::useIssuerFilterValidate($pmod, $noti['card_num']) === false)
            [$code, $message] = ['PV420', "이용할 수 없는 카드사입니다.", $pg_name];
        else if(PaymentTimeValidate::contractDateValidate($pmod) === false)
            [$code, $message] = ['PV426', '계약기간이 만료되었습니다.', $pg_name];
        else if(self::systemInspectionValidate() === false)
            [$code, $message] = ['PV427', '시스템 점검시간입니다.(06:00 ~ 06:05)', $pg_name];

        return [$code, $message, $pg_name];
    }

    // 빌키삭제 검증
    static public function deleteValidate($db, $pmod, $noti)
    {
        [$code, $message] = ['0000', ''];
        $pg_name = getPGType($pmod->pg_type);
        if(self::merchantStatusValidate($pmod) === false)
            [$code, $message] = ['PV412', '사용불가 가맹점 입니다.', $pg_name];
        else if(PaymentTimeValidate::contractDateValidate($pmod) === false)
            [$code, $message] = ['PV426', '계약기간이 만료되었습니다.', $pg_name];
        else if(self::systemInspectionValidate() === false)
            [$code, $message] = ['PV427', '시스템 점검시간입니다.(06:00 ~ 06:05)', $pg_name];

        return [$code, $message, $pg_name];
    }

    // 빌키결제 검증
    static public function payValidate($db, $pmod, $noti, $bill_key)
    {
        [$code, $message, $pg_name] = parent::validate($db, $pmod, $noti);
        if($code === '0000')
        {
            // 동일카드 중복결제 검증
             if(self::payDupeTrxValidate($db, $pmod, $bill_key->card_num) === false)
                [$code, $message] = ['PV411', "동일카드는 하루에 최대 ".$pmod->pay_dupe_limit."번만 결제가 가능합니다."];
            // 카드사 필터 검증
            else if(self::useIssuerFilterValidate($pmod, $bill_key->card_num) === false)
                [$code, $message] = ['PV420', "이용할 수 없는 카드사입니다."];
            else
            {
                // 결제 텀 검증
                $result = self::paymentTermValidate($db, $pmod, $noti);
                if($result['code'] === false)
                    [$code, $message] = ['PV421', '중복결제 방지를 위해 '.$result['able_at'].'초 부터 결제 가능합니다.'];
            }
            // 결제실패 추가
            if($code !== '0000')
                $code = self::addFailTransaction($pmod, $noti, 0, $code, $message);
        }
        return [$code, $message, $pg_name];
    }
}
