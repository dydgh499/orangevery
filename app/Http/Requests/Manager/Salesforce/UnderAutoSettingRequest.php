<?php

namespace App\Http\Requests\Manager\Salesforce;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class UnderAutoSettingRequest extends FormRequest
{
    use FormRequestTrait;
    
    public $keys = [
        'note',
    ];
    public $integer_keys = [
        'sales_id',
        'sales_fee',
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
            'note' => 'required',
            'sales_fee' => 'required|numeric',
        ];
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }

    public function bodyParameters()
    {
        return array_merge($this->getDocsParameters($this->keys), $this->getDocsParameters($this->integer_keys));
    }
    
    public function data()
    {
        $data = array_merge($this->getParmasBaseKey(), $this->getParmasBaseKeyV2($this->integer_keys, 0));
        $data['brand_id'] = $this->user()->brand_id;
        $data['sales_fee'] = $this->input('sales_fee', 0)/100;
        return $data;
    }
}
