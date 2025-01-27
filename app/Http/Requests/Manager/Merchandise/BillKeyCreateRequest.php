<?php

namespace App\Http\Requests\Manager\Merchandise;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BillKeyCreateRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'card_num',
        'yymm',
        'auth_num',
        'card_pw',
        'ord_num',
        'buyer_name',
        'buyer_phone',
    ];

    public $integer_keys = [
        'pmod_id',
    ];

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $sub = [
            'pmod_id' => 'required|integer',
            'card_num' => 'required',
            'yymm' => 'required',
            'auth_num' => 'required',
            'card_pw' => 'required',
            'ord_num' => 'required',
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

    public function data()
    {
        $getYYMM = function($mmyy) {
            if(mb_strlen($mmyy, 'utf-8') == 4)
            {
                $first 	= substr($mmyy, 0, 2);
                $sec 	= substr($mmyy, 2, 2);
                return $sec.$first;
            }
            else
                return '';
        };
        $data = array_merge($this->getParmasBaseKeyV2($this->keys, ''), $this->getParmasBaseKeyV2($this->integer_keys, 0));
        $data['yymm'] = $getYYMM($data['yymm']);
        return $data;
    }
}
