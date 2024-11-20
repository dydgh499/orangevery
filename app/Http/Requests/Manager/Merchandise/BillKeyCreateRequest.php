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
        return $this->user()->tokenCan(35) ? true : false;
    }

    public function rules()
    {
        $sub = [
            'pmod_id' => 'required',
            'card_num' => 'required',
            'yymm' => 'required',
            'auth_num' => 'required',
            'card_pw' => 'required',
            'ord_num' => 'required',
            'buyer_name' => 'required',
            'buyer_phone' => 'required',
        ];
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return array_merge($this->getAttributes($this->keys), $this->getAttributes($this->integer_keys));
    }

    public function bodyParameters()
    {
        return array_merge($this->getDocsParameters($this->keys), $this->getDocsParameters($this->integer_keys));
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
        $data = array_merge($this->getParmasBaseKeyV2($this->keys), $this->getParmasBaseKeyV2($this->integer_keys));
        $data['yymm'] = $getYYMM($data['yymm']);
        return $data;
    }
}
