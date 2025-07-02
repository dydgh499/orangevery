<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class TransactionRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'mid',
        'tid',
        'buyer_name',   //
        'buyer_phone',  //
        'item_name',    //
    ];
    public $integer_keys = [
        'pmod_id', 
        'pg_id', 
        'ps_id',
        'ps_fee',
        'installment',
        'module_type',
        'cxl_seq',
        'is_cancel',
        'amount',
    ];
    public $nullable_keys = [
    ];
    public function authorize()
    {
        return $this->user()->tokenCan(35) ? true : false;
    }

    /**
     * Get the validation rules that apply to the this.
     *
     * @return array
     */
    public function rules()
    {
        $sub = [
            'module_type' => 'required',
            'pg_id' => 'required', 
            'pmod_id' => 'required', 
            'ps_id' => 'required', 
            'ps_fee' => 'required',
            'amount' => 'required',
            'installment' => 'required',
        ];
        return $this->getRules($this->keys, $sub);
    }
    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }

    public function bodyParameters()
    {
        return $this->getDocsParameters($this->keys);
    }
    
    public function data()
    {
        $data = array_merge(
            $this->getParmasBaseKey(), 
            $this->getParmasBaseKeyV2($this->integer_keys, 0), 
            $this->getParmasBaseKeyV2($this->nullable_keys, null)
        );
        $data['ps_fee']  = $this->input('ps_fee', 0)/100;
        $data['brand_id'] = $this->user()->brand_id;
        return $data;
    }
}
