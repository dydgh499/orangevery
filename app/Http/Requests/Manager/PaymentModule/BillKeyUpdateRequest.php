<?php

namespace App\Http\Requests\Manager\PaymentModule;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BillKeyUpdateRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'buyer_name',
        'buyer_phone',
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
        return array_merge($this->getParmasBaseKeyV2($this->keys, ''), $this->getParmasBaseKeyV2($this->integer_keys, 0));
    }
}
