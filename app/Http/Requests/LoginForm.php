<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

class LoginForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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
        return [
            'email'     => 'required',
            'password'  => 'required',
        ];
    }
    public function attributes()
    {
        return [
            'email'    => __('email'),
            'password' => __('password'),
        ];
    }
    public function data()
    {
        $data = [
            'email'     => $this->input('email'),
            'password'  => $this->input('password'),
            'level'     => 0,
            'group_id'  => 0,
        ];
        return $data;
    }
}
