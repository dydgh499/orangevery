<?php

namespace App\Http\Requests\Manager\Merchandise\ShoppingMall;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;
use App\Http\Controllers\Ablilty\Ablilty;

class CategoryRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'category_name',
    ];

    public $integer_keys = [
        'mcht_id'
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $sub = [
            'mcht_id' => 'required',
            'category_name' => 'required',
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
        return array_merge($this->getParmasBaseKey(), $this->getParmasBaseKeyV2($this->integer_keys, 0));
    }
}
