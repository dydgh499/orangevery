<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class OperatorReqeust extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'user_name',
        'nick_name',
        'phone_num',
        'level',
    ];

    public $image_keys = [
        'profile_img',
    ];
    
    public $integer_keys = [
        'is_notice_realtime_warning',
        'is_active',
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
            'nick_name' => 'required',
            'phone_num' => 'required',
            'level' => 'required',
            'profile_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp',
        ];
        return $this->getRules(array_merge($this->keys, $this->integer_keys, $this->image_keys), $sub);
    }

    public function attributes()
    {
        return $this->getAttributes(array_merge($this->keys, $this->integer_keys, $this->image_keys));
    }

    public function bodyParameters()
    {
        $params = $this->getDocsParameters(array_merge($this->keys, $this->integer_keys, $this->image_keys));
        return $params;
    }

    public function data()
    {
        $data = array_merge($this->getParmasBaseKey(), $this->getParamsBaseFile($this->image_keys));
        $data['brand_id'] = $this->user()->brand_id;
        return $data;
    }
}
