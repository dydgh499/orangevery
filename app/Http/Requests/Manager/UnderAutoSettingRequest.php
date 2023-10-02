<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class UnderAutoSettingRequest extends FormRequest
{
    use FormRequestTrait;
    
    public function __construct()
    {
        $this->keys = [
            'note',
            'note',
        ];
        $this->integer_keys = [
            'sales_id',
            'sales0_fee',
            'sales0_id',
            'sales1_fee',
            'sales1_id',
            'sales2_fee',
            'sales2_id',
            'sales3_fee',
            'sales3_id',
            'sales4_fee',
            'sales4_id',
            'sales5_fee',
            'sales5_id',
            'sales_id',
        ];
    }

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
        $data = array_merge($this->getParmasBaseKey(), $this->getParmasBaseKeyV2($this->integer_keys, 0));
        $data['brand_id'] = $this->user()->brand_id;
        $data['sales0_fee'] = $this->input('sales0_fee', 0)/100;
        $data['sales1_fee'] = $this->input('sales1_fee', 0)/100;
        $data['sales2_fee'] = $this->input('sales2_fee', 0)/100;
        $data['sales3_fee'] = $this->input('sales3_fee', 0)/100;
        $data['sales4_fee'] = $this->input('sales4_fee', 0)/100;
        $data['sales5_fee'] = $this->input('sales5_fee', 0)/100;
        return $data;
    }
}
