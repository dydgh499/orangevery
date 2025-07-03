<?php

namespace App\Http\Requests\External;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class SignUpRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        // IP 정보
        'request_ip',
        // 운영자 정보
        'user_name',
        'user_pw',
        'nick_name',
        'phone_num',
        // 결제모듈 정보
        'mid',
        'tid',
        'pay_key',
    ];

    public $integer_keys = [
        // 수수료율 정보
        'trx_fee',
    ];
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $sub = [
            'request_ip'    => 'string|required',
            'user_name'     => 'string|required',
            'user_pw'       => 'string|required',
            'nick_name'     => 'string|required',
            'phone_num'     => 'string|required',
            'mid'           => 'string|required',
            'tid'           => 'string|required',
            'pay_key'       => 'string|required',
            'trx_fee'       => 'numeric|required',
        ];
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }
    
    public function bodyParameters()
    {
        return $this->getDocsParameters($this->keys);
    }
}
