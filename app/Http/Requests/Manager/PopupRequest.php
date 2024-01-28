<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class PopupRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'popup_title',
        'popup_content',
        'open_s_dt',
        'open_e_dt',
    ];

    public function authorize()
    {
        return $this->user()->tokenCan(10) ? true : false;
    }

    public function rules()
    {
        $sub = [
            'popup_title' => 'required',
            'popup_content' => 'required',
            'open_s_dt' => 'required',
            'open_e_dt' => 'required',
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

    protected function prepareForValidation()
    {

    }

    public function data()
    {
        $data = $this->getParmasBaseKey();
        $data['brand_id'] = $this->user()->brand_id;
        return $data;
    }
}
