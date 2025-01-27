<?php

namespace App\Http\Requests\Manager\Merchandise;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BillKeyIndexRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'buyer_name',
        'buyer_phone',
        'request_at',
    ];

    public $integer_keys = [];

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $sub = [
            'buyer_name' => 'required',
            'buyer_phone' => 'required',
            'request_at' => 'required',
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
        $params['request_at']['example']    = '2025-01-06 00:49:32';

        $params['buyer_name']['description']    = '요청당시 인증자 휴대폰 번호';
        $params['buyer_phone']['description']   = '요청당시 인증자 성명';
        $params['request_at']['description']    = '본인인증 요청시간';
        return $params;
    }
}
