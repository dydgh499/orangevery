<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class OperatorReqeust extends FormRequest
{
    use FormRequestTrait;
    
    public function __construct()
    {
        $this->keys = [
            'user_name',
            'nick_name',
            'phone_num',
            'level',
        ];
    }

    public function authorize()
    {
        return $this->user()->tokenCan(30) ? true : false;
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
            'nick_name' => 'required',
            'phone_num' => 'required',
            'level' => 'required',
            'profile_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp',
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
        $data = $this->getParmasBaseKey();
        $data['brand_id'] = $this->user()->brand_id;
        if($this->has('profile_img'))
            $data['profile_img'] = $this->profile_img;
        return $data;
    }
}
