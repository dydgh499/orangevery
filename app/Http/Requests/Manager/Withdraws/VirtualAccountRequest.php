<?php

namespace App\Http\Requests\Manager\Withdraws;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class VirtualAccountRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'account_name',
        'account_code',
    ];
    public $integer_keys = [
        'fin_id',
        'fin_trx_delay',
        'withdraw_fee',
        'withdraw_type',
        'withdraw_limit_type',
        'withdraw_business_limit',
        'withdraw_holiday_limit',
        'level',
    ];

    public function authorize()
    {
        return $this->user()->tokenCan(35) ? true : false;
    }

    public function rules()
    {
        $sub = [
            'fin_id' => 'required',
            'fin_trx_delay' => 'required',
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
        return array_merge($this->getParmasBaseKeyV2($this->keys, ''), $this->getParmasBaseKeyV2($this->integer_keys, 0));
    }
}
