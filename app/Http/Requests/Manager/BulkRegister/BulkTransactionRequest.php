<?php

namespace App\Http\Requests\Manager\BulkRegister;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BulkTransactionRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'acct_num', // 입금 계좌번호
        'acct_name', // 예금주
        'acct_bank_code', // 은행코드
    ];
    public $integer_keys = [
        'bill_id',
        'amount',
    ];

    public function bodyParameters()
    {
        return array_merge($this->getDocsParameters($this->keys));
    }

    public function authorize()
    {
        return $this->user()->tokenCan(35) ? true : false;
    }

    public function rules()
    {
        $sub = [
            '*' => 'required|array',
            '*.acct_num' => 'required', // 입금 계좌번호
            '*.acct_name' => 'required', // 예금주
            '*.acct_bank_code' => 'required', // 은행코드
            '*.bill_id' => 'required', // 빌키
            '*.amount' => 'required', // 결제금액
        ];
        return $sub;
    }
    
    public function data()
    {
        $datas  = [];
        $_datas = $this->all();
        for ($i=0; $i < count($_datas) ; $i++)
        {
            $data = array_merge(
                $this->getParmasBaseKeyV3($_datas[$i], $this->keys, ''),
                $this->getParmasBaseKeyV3($_datas[$i], $this->integer_keys, 0),
            );
            $datas[] = $data;
        }
        return collect($datas);
    }
}
