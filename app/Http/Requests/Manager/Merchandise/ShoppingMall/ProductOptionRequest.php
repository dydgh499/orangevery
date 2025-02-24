<?php

namespace App\Http\Requests\Manager\Merchandise\ShoppingMall;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;
use App\Http\Controllers\Ablilty\Ablilty;

class ProductOptionRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'option_name',
    ];
    public $boolean_keys = [];
    public $nullable_keys = [];
    
    public $integer_keys = [
        'group_id',
        'option_price',
    ];

    public function authorize()
    {
        if(Ablilty::isOperator($this))
            return true; 
        else if(Ablilty::isMerchandise($this))
            return true;
        else
            return false;
    }

    public function rules()
    {
        $sub = [
            'group_id' => 'required',
            'option_price' => 'required',
            'option_name' => 'required',
        ];
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return array_merge($this->getAttributes($this->keys), $this->getAttributes($this->integer_keys));
    }

    public function bodyParameters()
    {
        return array_merge($this->getDocsParameters($this->keys), $this->getDocsParameters($this->integer_keys));
    }

    public function data()
    {
        return array_merge(
            $this->getParmasBaseKeyV2($this->keys, ''), $this->getParmasBaseKeyV2($this->integer_keys, 0)
        );
    }
}
