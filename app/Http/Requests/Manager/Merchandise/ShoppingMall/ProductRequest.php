<?php

namespace App\Http\Requests\Manager\Merchandise\ShoppingMall;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;
use App\Http\Controllers\Ablilty\Ablilty;

class ProductRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'product_name',
        'content',
    ];
    public $boolean_keys = [];
    public $nullable_keys = [];
    
    public $integer_keys = [
        'pmod_id',
        'category_id',
        'product_amount',
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
            'category_id' => 'required',
            'product_amount' => 'required',
            'product_name' => 'required',
            'content' => 'required',
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
