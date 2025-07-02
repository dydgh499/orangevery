<?php

namespace App\Http\Requests\Manager\Service;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class PayGatewayRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'pg_name',
        'rep_name',
        'company_name',
        'business_num',
        'phone_num',
        'addr',
    ];
    public $integer_keys = [
        'pg_type',
    ];

    public function authorize()
    {
        return $this->user()->tokenCan(10) ? true : false;
    }

    public function rules()
    {
        $sub = [
            'pg_type' => 'required',
            'pg_name' => 'required',
            'rep_name' => 'nullable',
            'company_name' => 'nullable',
            'business_num' => 'nullable',
            'phone_num' => 'nullable',
            'addr' => 'nullable',
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
        $data = array_merge($this->getParmasBaseKeyV2($this->keys, ''), $this->getParmasBaseKeyV2($this->integer_keys, 0));
        $data['brand_id'] = $this->user()->brand_id;
        return $data;
    }
}
