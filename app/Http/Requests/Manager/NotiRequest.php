<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class NotiRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'mcht_id',
        'send_url',
        'noti_status',
        'note',
    ];

    public function authorize()
    {
        return $this->user()->tokenCan(10) ? true : false;
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
            'send_url' => 'required',
            'noti_status' => 'required',
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
        return $data;
    }
}
