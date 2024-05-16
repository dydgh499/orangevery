<?php

namespace App\Http\Requests\Manager\Service;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class HeadOfficeAccountRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'acct_bank_name',
        'acct_bank_code',
        'acct_num',
        'acct_name',
    ];

    public function authorize(): bool
    {
        return $this->user()->tokenCan(10) ? true : false;
    }

    public function rules(): array
    {
        $sub = [
            'acct_bank_name' => 'required',
            'acct_bank_code' => 'required',
            'acct_num' => 'required',
            'acct_name' => 'required',
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
