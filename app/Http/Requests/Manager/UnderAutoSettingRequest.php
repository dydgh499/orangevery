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
        ];
        $this->integer_keys = [
            'sales_id',
            'sales_fee',
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
        $params = $this->getDocsParameters($this->keys);
        return $params;
    }
    public function data()
    {
        $data = array_merge($this->getParmasBaseKey(), $this->getParmasBaseKeyV2($this->integer_keys, 0));
        $data['brand_id'] = $this->user()->brand_id;
        $data['sales_fee'] = $this->input('sales_fee', 0)/100;
        return $data;
    }
}
