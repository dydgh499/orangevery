<?php
namespace App\Http\Controllers\Utils;

use App\Jobs\Realtime\Dozn\DoznWrap;
use App\Jobs\Realtime\Hecto\HectoWrap;
use App\Jobs\Realtime\Hyphen\HyphenWrap;
use App\Jobs\Realtime\Coocon\CooconWrap;
use App\Jobs\Realtime\Welcome\WelcomeWrap;
use App\Jobs\Realtime\Nestpay\NestpayWrap;

use App\Models\Service\FinanceVan;

use Carbon\Carbon;

class FinanceVanUtil
{
    static public function getFianceVan($fin_id)
    {
        return FinanceVan::where('id', $fin_id)->first();
    }

    static public function getPrivacy($mcht)
    {
        return [
            'acct_num' => $mcht->acct_num,
            'acct_name' => $mcht->acct_name,
            'acct_bank_name' => $mcht->acct_bank_name,
            'acct_bank_code' => $mcht->acct_bank_code,
        ];
    }

    # 브랜드, 개인정보 리턴
    static public function getThirdPartyInfo($brand, $mcht)
    {
        $finance_van    = FinanceVanUtil::getFianceVan($brand->fin_id);
        $privacy        = FinanceVanUtil::getPrivacy($mcht);
        if($finance_van && $privacy)
        {
            $finance_van = json_decode(json_encode($finance_van), true);
            return ['0000', $finance_van, $privacy];
        }
        else
            return ['PV450', [], []];
    }

    static public function getFinanceVanModule($finance_van, $privacy, $deposit_type, $withdraw_book_time)
    {
        if($finance_van['finance_company_num'] === 1)
            $rt = new CooconWrap($finance_van, $privacy, $deposit_type, $withdraw_book_time);
        else
            return null;
        return $rt;
    }
}
