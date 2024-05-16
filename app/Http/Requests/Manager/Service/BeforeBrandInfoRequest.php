<?php

namespace App\Http\Requests\Manager\Service;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BeforeBrandInfoRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'company_name',
        'rep_name',
        'phone_num',
        'business_num',
        'addr',
        'apply_s_dt',
        'apply_e_dt',
    ];

    public function authorize(): bool
    {
        return $this->user()->tokenCan(10) ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $sub = [
            'company_name' => 'required',
            'rep_name' => 'required',
            'phone_num' => 'required',
            'business_num' => 'required',
            'addr' => 'required',
            'apply_s_dt' => 'required',
            'apply_e_dt' => 'required',
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
