<?php

namespace App\Http\Requests\Manager\Pay;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BillKeyPayRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'ord_num',
        'item_name',
        'buyer_name',
        'buyer_phone',
        'amount',
        'addr',
        'detail_addr',
        'option_groups',
        'note',
    ];

    public $integer_keys = [
        'installment',
    ];

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $sub = [
            'pmod_id' => 'required',
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
        $data = array_merge($this->getParmasBaseKeyV2($this->keys, ''), $this->getParmasBaseKeyV2($this->integer_keys, 0));
        return $data;
    }
}
