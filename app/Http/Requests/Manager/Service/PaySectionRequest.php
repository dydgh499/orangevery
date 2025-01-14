<?php

namespace App\Http\Requests\Manager\Service;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class PaySectionRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'pg_id',
        'name',
        'trx_fee',
    ];

    public function authorize()
    {
        return $this->user()->tokenCan(10) ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $sub = [
            'pg_id' => 'required',
            'name' => 'required',
            'trx_fee' => 'required',
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
        $data['trx_fee'] = (float)$data['trx_fee']/100;
        return $data;
    }
}
