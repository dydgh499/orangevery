<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class ClassificationReqeust extends FormRequest
{
    use FormRequestTrait;
    
    public function __construct()
    {
        $this->keys = [
            'type',
            'name',
            'trx_fee',
        ];
    }

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
            'type' => 'required',
            'name' => 'required',
            'trx_fee' => 'required',
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
        $data = [];
        for ($i=0; $i < count($this->keys) ; $i++)
        {
            $key = $this->keys[$i];
            $data[$key] = $this->input($key, '');
        }
        $data['brand_id'] = $this->user()->brand_id;
        $data['is_use'] = true;
        return $data;
    }
}
