<?php

namespace App\Http\Requests\External;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class SignCheckRequest extends FormRequest
{
    use FormRequestTrait;

    public $keys = [
        // 운영자 정보
        'user_name',
    ];
    public $integer_keys = [];

    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $sub = [
            'user_name'     => 'string|required',
        ];
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }

    public function bodyParameters()
    {
        return $this->getDocsParameters($this->keys);
    }
}
