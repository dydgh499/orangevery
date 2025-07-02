<?php

namespace App\Http\Requests\Manager\Merchandise;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;
use App\Http\Controllers\Ablilty\Ablilty;

class PayModuleRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'pg_id',
        'ps_id',
        'module_type',
        'api_key',
        'sub_key',
        'mid',
        'tid',
        'installment',
        'note',
    ];
    public $boolean_keys = [
        'is_old_auth',
    ];
    public $nullable_keys = [];
    
    public $integer_keys = [];

    public function authorize()
    {
        if(Ablilty::isOperator($this))
            return true;
        else
            return false;
    }

    public function rules()
    {
        $sub = [
            'pg_id' => 'required',
            'ps_id' => 'required',
            'mid' => 'required',
            'module_type' => 'required',
            'note' => 'required',
        ];
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }

    public function bodyParameters()
    {
        return array_merge($this->getDocsParameters($this->keys), $this->getDocsParameters($this->boolean_keys), $this->getDocsParameters($this->nullable_keys));
    }

    public function data()
    {
        $data_1 = array_merge($this->getParmasBaseKeyV2($this->nullable_keys, null), $this->getParmasBaseKeyV2($this->boolean_keys, 0));
        $data_2 = array_merge($this->getParmasBaseKey(), $this->getParmasBaseKeyV2($this->integer_keys, 0));
        $data   = array_merge($data_1, $data_2);
        $data['note'] = $data['note'] == null ? '' : $data['note'];
        $data['brand_id'] = $this->user()->brand_id;
        return $data;
    }
}
