<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class FinanceRequest extends FormRequest
{
    use FormRequestTrait;

    public function __construct()
    {
        $this->keys = [];
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return $this->user()->tokenCan(10) ? true : false;
    }

    public function rules()
    {
        $sub = [

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
