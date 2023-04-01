<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class LoginForm extends FormRequest
{
    use FormRequestTrait;

    public function __construct()
    {
        $this->keys = ['brand_id', 'user_name','user_pw','login_type'];
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $sub = [
            'brand_id'  => 'required',
            'user_name' => 'required',
            'user_pw'   => 'required',
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

    public function data($level=0, $bid)
    {
        $data = [
            'brand_id'  => $bid,
            'user_name' => $this->input('user_name'),
            'user_pw'   => Hash::make($this->input('user_pw')),
            'level'     => $level,
            'group_id'  => $this->input('group_id', 0),
        ];
        return $data;
    }
}
