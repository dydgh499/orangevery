<?php

namespace App\Http\Requests\Manager\Service;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class OperatorIPRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = ['enable_ip'];

    public function authorize(): bool
    {
        return $this->user()->tokenCan(40) ? true : false;
    }

    public function rules(): array
    {
        $sub = ['enable_ip' => 'required'];
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
        return $this->getParmasBaseKey();
    }
}
