<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class FinanceRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'finance_company_num',
        'fin_type',
        'dev_fee',
        'api_key',
        'sub_key',
        'enc_key',
        'iv',
        'min_balance_limit',
        'corp_code',
        'corp_name',
        'bank_code',
        'nick_name',
        'withdraw_acct_num',
    ];

    public function authorize()
    {
        return $this->user()->tokenCan(35) ? true : false;
    }

    public function rules()
    {
        $sub = [
            'finance_company_num' => 'required',
            'fin_type' => 'required',
            'dev_fee' => 'required|numeric',
            'min_balance_limit'=>'required',
            'corp_name'=>'required',
            'nick_name'=>'required',
        ];
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }

    public function bodyParameters()
    {
        $params = $this->getDocsParameters($this->keys);
        return $params;
    }

    public function data()
    {
        $data = $this->getParmasBaseKey();
        $data['dev_fee'] = $data['dev_fee']/100;
        $data['brand_id'] = $this->user()->brand_id;        
        return $data;
    }
}
