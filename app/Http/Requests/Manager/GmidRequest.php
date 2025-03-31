<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class GmidRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'user_name',
        'phone_num',
        'nick_name',
        'g_mid',
        'profile_img',
    ];
    public $file_keys = [
        'profile_file',
    ];

    public function authorize()
    {
        return $this->user()->tokenCan(35) ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $sub = [
            'user_name' => 'required',
        ];
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }

    public function bodyParameters()
    {
        return array_merge($this->getDocsParameters($this->keys), $this->getDocsParameters($this->file_keys));
    }

    public function data()
    {
        return $this->getParmasBaseKeyV2($this->keys, '');
    }
}
