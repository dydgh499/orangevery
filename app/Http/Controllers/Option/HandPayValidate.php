<?php
namespace App\Http\Controllers\Option;

use App\Http\Controllers\Ablilty\BrandInfo;
use App\Http\Controllers\Option\PayValidate;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class HandPayValidate extends PayValidate
{
    // 구인증 결제 검증
    static public function oldAuthValidate($noti)
    {
        if($noti['is_old_auth'])
        {
            $auth_num = $noti['auth_num'] ? true : false;
            $card_pw = $noti['card_pw'] ? true : false;
            return $auth_num && $card_pw;
        }
        return true;
    }

    // 동일카드 중복결제 검증
    static public function payDupeTrxValidate($db, $pmod, $card_num)
    {
        if($pmod->pay_dupe_limit)
        {
            $bin    = substr($card_num, 0, 6);
            $last4  = substr($card_num, -4);
            $count = $db->table('transactions')
                ->where('pmod_id', $pmod->id)
                ->where('mcht_id', $pmod->mcht_id)
                ->where('brand_id', $pmod->brand_id)
                ->where('trx_at', '>=', date('Y-m-d 00:00:00'))
                ->where('is_cancel', false)
                ->where(function ($query) use ($bin, $last4) {
                    return $query->where('card_num', 'like', "$bin"."%")
                        ->where('card_num', 'like', "%"."$last4");
                })
                ->count();
            if($count >= $pmod->pay_dupe_limit)
                return false;
        }
        return true;
    }

    // 등록 카드 검증
    static public function useRegularCardValidate($db, $pmod, $card_num)
    {
        if($pmod->use_regular_card)
        {
            return $db->table('regular_credit_cards')
                ->where('mcht_id', $pmod->mcht_id)
                ->pluck('card_num')
                ->contains($card_num);
        }
        else
            return true;
    }

    // 카드사 검증
    static public function useIssuerFilterValidate($pmod, $card_num)
    {
        $cards = json_decode($pmod->filter_issuers, true);
        if(strlen($card_num) > 10)
        {
            if($cards !== null && count($cards) > 0)
            {
                $path = Storage::disk('public')->path('issuers.json');
                $content = file_get_contents($path);
                $issuers = json_decode($content, true);

                foreach($cards as $card)
                {
                    foreach($issuers as $issuer)
                    {
                        if(strpos($issuer['name'], $card) !== false)
                        {
                            $bin = substr($card_num, 0 ,6);
                            $idx = array_search($bin, array_column($issuer['cards'], 'value'));
                            if($idx !== false)
                                return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    // 결제 텀 검증 (동일 결제모듈, 동일카드, 동일금액)
    static public function paymentTermValidate($db, $pmod, $noti)
    {
        $result = [
            'code' => true,
            'able_at' => '',
        ];
        if($pmod->payment_term_min)
        {
            $bin    = substr($noti['card_num'], 0, 6);
            $last4  = substr($noti['card_num'], -4);
            $trx_at = Carbon::now()->subMinutes($pmod->payment_term_min)->format('Y-m-d H:i:s');

            $trans = $db->table('transactions')
                ->where('pmod_id', $pmod->id)
                ->where('mid', $pmod->mid)
                ->where('mcht_id', '>=', $pmod->mcht_id)
                ->where('brand_id', $pmod->brand_id)
                ->where('trx_at', '>=', $trx_at)
                ->where('amount', $noti['amount'])
                ->where(function ($query) use ($bin, $last4) {
                    return $query->where('card_num', 'like', "$bin"."%")
                        ->where('card_num', 'like', "%"."$last4");
                })
                ->first(['trx_at']);
            if($trans)
            {
                $result['code'] = false;
                $result['able_at'] = Carbon::createFromFormat('Y-m-d H:i:s', $trans->trx_at)->addMinutes($pmod->payment_term_min)->format("H:i:s");
                return $result;
            }
            else
                return $result;
        }
        else
            return $result;
    }

    // 가맹점 블랙리스트 카드번호 검증
    static public function blacklistCardNumValidate($db, $pmod, $card_num)
    {
        if(strlen($card_num) > 10)
        {
            $brand = BrandInfo::getBrandById($pmod->brand_id);
            if($brand['pv_options']['paid']['use_mcht_blacklist'])
            {
                $bin    = substr($card_num, 0, 6);
                $last4  = substr($card_num, -4);
                $count = $db->table('mcht_blacklists')
                    ->where(function ($query) use ($bin, $last4) {
                        return $query->where('card_num', 'like', "$bin"."%")
                            ->where('card_num', 'like', "%"."$last4");
                    })->count();
                return $count ? false : true;
            }
            else
                return true;
        }
        return true;
    }

    // 수기결제 검증
    static public function validate($db, $pmod, $noti)
    {
        [$code, $message, $pg_name] = parent::validate($db, $pmod, $noti);
        if($code === '0000')
        {
            // 구인증 필수값 검증
            if(self::oldAuthValidate($noti) === false)
                [$code, $message] = ['PV414', '카드비밀번호 또는 생년월일(사업자번호)를 입력해주세요.'];
            // 동일카드 중복결제 검증
            else if(self::payDupeTrxValidate($db, $pmod, $noti['card_num']) === false)
                [$code, $message] = ['PV411', "동일카드는 하루에 최대 ".$pmod->pay_dupe_limit."번만 결제가 가능합니다."];
            // 등록 카드 검증
            else if(self::useRegularCardValidate($db, $pmod, $noti['card_num']) === false)
                [$code, $message] = ['PV416', "등록된 카드가 아니므로 결제할 수 없습니다."];
            // 카드사 필터 검증
            else if(self::useIssuerFilterValidate($pmod, $noti['card_num']) === false)
                [$code, $message] = ['PV420', "이용할 수 없는 카드사입니다."];
            else
            {
                // 결제 텀 검증
                $result = self::paymentTermValidate($db, $pmod, $noti);
                if($result['code'] === false)
                    [$code, $message] = ['PV421', '중복결제 방지를 위해 '.$result['able_at'].'초 부터 결제 가능합니다.'];
                // 블랙리스트 검증
                else if(self::blacklistCardNumValidate($db, $pmod, $noti['card_num']) === false)
                    [$code, $message] = ['PV423', '이용할 수 없는 카드입니다.'];
            }
            // 결제실패 추가
            if($code !== '0000')
                $code = self::addFailTransaction($pmod, $noti, 0, $code, $message);
        }
        return [$code, $message, $pg_name];
    }

    static public function getPayFormat($pmod, $request)
    {
        return array_merge(parent::getDefaultPayFormat($pmod, $request), [
            'is_old_auth'=> $pmod->is_old_auth,
            'card_num'  => $request->card_num,
            'yymm'      => $request->yymm,
            'auth_num'  => $request->auth_num,
            'card_pw'   => $request->card_pw,
        ]);
    }
}
