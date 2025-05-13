<?php

namespace App\Http\Requests\Manager\BulkRegister;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BulkBankAccountRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'acct_bank_name', // 입금 은행명
        'acct_num', // 입금 계좌번호
        'acct_name', // 예금주
        'acct_bank_code', // 은행코드
        'note', // 출금 사유
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
            '*.acct_bank_name' => 'required', // 입금 은행명
            '*.acct_num' => 'required', // 입금 계좌번호
            '*.acct_name' => 'required', // 예금주
            '*.acct_bank_code' => 'required', // 은행코드
        ];
        return $sub;
    }
    
    public function data()
    {
        $datas = [];
        $_datas = $this->all();
        for ($i=0; $i < count($_datas) ; $i++)
        {
            $data = array_merge(
                $this->getParmasBaseKeyV3($_datas[$i], $this->keys, '')
            );
            $datas[] = $data;
        }
        return collect($datas);
    }
}
