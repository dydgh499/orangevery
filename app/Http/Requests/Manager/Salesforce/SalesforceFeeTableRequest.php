<?php

namespace App\Http\Requests\Manager\Salesforce;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class SalesforceFeeTableRequest extends FormRequest
{
    use FormRequestTrait;
    
    public $keys = [];

    public $nullable_keys = [
        'sales5_id',
        'sales4_id',
        'sales3_id',
        'sales2_id',
        'sales1_id',
    ];

    public $integer_keys = [
        'sales5_fee',
        'sales4_fee',
        'sales3_fee',
        'sales2_fee',
        'sales1_fee',
    ];

    public function authorize()
    {
        return $this->user()->tokenCan(13) ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $sub = [
            'sales5_id' => 'required|numeric',
            'sales4_id' => 'required|numeric',
            'sales3_id' => 'required|numeric',
            'sales2_id' => 'required|numeric',
            'sales1_id' => 'required|numeric',
            
            'sales5_fee' => 'required|numeric',
            'sales4_fee' => 'required|numeric',
            'sales3_fee' => 'required|numeric',
            'sales2_fee' => 'required|numeric',
            'sales1_fee' => 'required|numeric',
        ];
        return $this->getRules(array_merge($this->nullable_keys, $this->integer_keys), $sub);
    }

    public function attributes()
    {
        return $this->getAttributes(array_merge($this->nullable_keys, $this->integer_keys));
    }

    public function bodyParameters()
    {
        return array_merge($this->getDocsParameters($this->nullable_keys), $this->getDocsParameters($this->integer_keys));
    }
    
    public function data()
    {
        return array_merge(
            $this->getParmasBaseKeyV2($this->nullable_keys, null), 
            $this->getParmasBaseKeyV2($this->integer_keys, 0), ['brand_id' => $this->user()->brand_id]
        );
    }
}
