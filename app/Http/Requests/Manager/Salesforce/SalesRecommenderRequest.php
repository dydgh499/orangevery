<?php

namespace App\Http\Requests\Manager\Salesforce;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class SalesRecommenderRequest extends FormRequest
{
    use FormRequestTrait;
    
    public $keys = [
    ];
    public $integer_keys = [
        'sales_id',
        'mcht_fee',
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
            'sales_id' => 'required|integer',
            'mcht_fee' => 'required|numeric',
        ];
        return $this->getRules(array_merge($this->keys, $this->integer_keys), $sub);
    }

    public function attributes()
    {
        return $this->getAttributes(array_merge($this->keys, $this->integer_keys));
    }

    public function bodyParameters()
    {
        return array_merge($this->getDocsParameters($this->keys), $this->getDocsParameters($this->integer_keys));
    }
    
    public function data()
    {
        return array_merge($this->getParmasBaseKey(), $this->getParmasBaseKeyV2($this->integer_keys, 0));
    }
}
