<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class LoginRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = ['user_name','user_pw'];

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $sub = [
            'user_name' => 'required|string',
            'user_pw'   => 'required|string',
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
        $params['user_name']['example']    = 'test0001';
        $params['user_pw']['example']      = 'test0001';
        return $params;
    }
}
