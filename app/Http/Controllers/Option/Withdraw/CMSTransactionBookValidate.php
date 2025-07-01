<?php
namespace App\Http\Controllers\Option\Withdraw;

use App\Models\Service\CMSTransactionBooks;

class CMSTransactionBookValidate
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
