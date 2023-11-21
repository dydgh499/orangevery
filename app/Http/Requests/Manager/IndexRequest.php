<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\FormRequestTrait;

class IndexRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = ['page','page_size','s_dt', 'e_dt'];
    public $sub = [
        'page'      => 'required|integer',
        'page_size' => 'required|integer',
        's_dt'  => 'nullable|string',
        'e_dt'  => 'nullable|string',
    ];

    public function authorize()
    {
        return $this->user()->tokenCan(10) ? true : false;
    }

    public function rules()
    {
        return $this->getRules($this->keys, $this->sub);
    }

    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }

    public function queryParameters()
    {
        $this->params = $this->getDocsParameters($this->keys);
        $this->params['page']['example']      = 1;
        $this->params['page_size']['example'] = 20;
        $this->params['s_dt']['example']      = '2023-10-14';
        $this->params['e_dt']['example']      = '2023-10-15';
        return $this->params;
    }
    public function bodyParameters()
    {
        return [];
    }
}
