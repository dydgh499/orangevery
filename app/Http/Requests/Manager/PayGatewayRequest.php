<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class PayGatewayRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'pg_type',
        'pg_name',
        'rep_name',
        'company_name',
        'business_num',
        'phone_num',
        'addr',
        'settle_type',
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
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }

    public function bodyParameters()
    {
        $params = $this->getDocsParameters($this->keys);
        return $params;
    }

    public function data()
    {
        $data = $this->getParmasBaseKey();
        $data['brand_id'] = $this->user()->brand_id;
        return $data;
    }
}
