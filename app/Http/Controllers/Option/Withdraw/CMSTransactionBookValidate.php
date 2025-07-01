<?php
namespace App\Http\Controllers\Option\Withdraw;

use App\Http\Controllers\Option\Withdraw\CMSTransactionLimitValidate;

use App\Models\Service\CMSTransactionBooks;
use App\Models\Service\CMSTransaction;

class CMSTransactionBookValidate extends CMSTransactionLimitValidate
{
    static public function updateWithdraw($last_id, $result_code, $message)
    {
        $params = [
            'result_code'=> $result_code,
            //'message' => $message,
        ];
        return CMSTransactionBooks::where('id', $last_id)->update($params);
    }

}
