<?php

namespace App\Http\Requests\Manager\Service;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class IdentityAuthInfoRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'identitiy_auth_type',
        'corp_code',
        'api_key',
        'sub_key',
        'enc_key',
    ];

    public function authorize()
    {
        return $this->user()->tokenCan(35) ? true : false;
    }

    public function rules()
    {
        $sub = [
            'identitiy_auth_type' => 'required|integer',
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
