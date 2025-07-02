<?php

namespace App\Http\Requests\Manager\Pay;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BillKeyUpdateRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'nick_name',
        'buyer_name',
        'buyer_phone',
    ];

    public $integer_keys = [
    ];

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $sub = [
            'buyer_name' => 'required',
            'buyer_phone' => 'required',
        ];
        return $this->getRules(array_merge($this->keys, $this->integer_keys), $sub);
    }

    public function attributes()
    {
        return array_merge($this->getAttributes($this->keys), $this->getAttributes($this->integer_keys));
    }

    public function bodyParameters()
    {
        $params = array_merge($this->getDocsParameters($this->keys), $this->getDocsParameters($this->integer_keys));
        $params['buyer_name']['example']    = '홍길동';
        $params['buyer_phone']['example']   = '01012345678';

        $params['buyer_name']['description']    = '요청당시 인증자 휴대폰 번호';
        $params['buyer_phone']['description']   = '요청당시 인증자 성명';
        return $params;
    }

    public function data()
    {
        return array_merge($this->getParmasBaseKeyV2($this->keys, ''), $this->getParmasBaseKeyV2($this->integer_keys, 0));
    }
}
