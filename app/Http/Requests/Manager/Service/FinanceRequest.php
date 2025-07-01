<?php

namespace App\Http\Requests\Manager\Service;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class FinanceRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'finance_company_num',
        'api_key',
        'sub_key',
        'enc_key',
        'iv',
        'corp_code',
        'corp_name',
        'bank_code',
        'nick_name',
        'withdraw_acct_num',
        'deposit_type',
    ];

    public $integer_keys = [
        'is_agency_van',
        'min_balance_limit',
        'use_kakao_auth',
        'use_account_auth',
    ];

    public function authorize()
    {
        return $this->user()->tokenCan(35) ? true : false;
    }

    public function rules()
    {
        $sub = [
            'finance_company_num' => 'required',
            'min_balance_limit'=>'required',
            'corp_name'=>'required',
            'nick_name'=>'required',
        ];
        return $this->getRules(array_merge($this->keys, $this->integer_keys), $sub);
    }

    public function attributes()
    {
        return $this->getAttributes(array_merge($this->keys, $this->integer_keys));
    }

    public function bodyParameters()
    {
        return $this->getDocsParameters(array_merge($this->keys, $this->integer_keys));
    }

    public function data()
    {
        $data = array_merge($this->getParmasBaseKey(), $this->getParmasBaseKeyV2($this->integer_keys, 0));
        $data['brand_id'] = $this->user()->brand_id;        
        return $data;
    }
}
