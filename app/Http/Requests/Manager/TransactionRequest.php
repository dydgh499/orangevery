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
        'mcht_id',
        'pmod_id', 
        'pg_id', 
        'ps_id',
        'sales5_fee',
        'sales4_fee',
        'sales3_fee',
        'sales2_fee',
        'sales1_fee',
        'sales0_fee',
        'dev_realtime_fee',
        'mcht_fee', 
        'hold_fee',
        'ps_fee',
        'installment',
        'module_type',
        'cxl_seq',
        'mcht_settle_type',
        'is_cancel',
        'amount',
        'mcht_settle_fee',
        'pg_settle_type',
        'round_type',
    ];
    public $nullable_keys = [
        'custom_id', 
        'terminal_id',
        'sales0_id',
        'sales1_id',
        'sales2_id',
        'sales3_id',
        'sales4_id',
        'sales5_id',
        'cxl_dt',
        'cxl_tm',
        'trx_dt',
        'trx_tm',
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
            'mcht_id' => 'required',
            'mcht_fee' => 'required', 
            'hold_fee' => 'required',
            'module_type' => 'required',
            'pg_id' => 'required', 
            'pmod_id' => 'required', 
            'ps_id' => 'required', 
            'ps_fee' => 'required',
            'mcht_settle_fee' => 'required', 
            'mcht_settle_type'=> 'required',
            'trx_dt' => 'required', 
            'trx_tm' => 'required',
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
        $data['hold_fee']  = $this->input('hold_fee', 0)/100;
        $data['mcht_fee']    = $this->input('mcht_fee', 0)/100;
        $data['sales0_fee'] = $this->input('sales0_fee', 0)/100;
        $data['sales1_fee'] = $this->input('sales1_fee', 0)/100;
        $data['sales2_fee'] = $this->input('sales2_fee', 0)/100;
        $data['sales3_fee'] = $this->input('sales3_fee', 0)/100;
        $data['sales4_fee'] = $this->input('sales4_fee', 0)/100;
        $data['sales5_fee'] = $this->input('sales5_fee', 0)/100;
        $data['dev_fee']    = $this->input('dev_fee', 0)/100;
        $data['dev_realtime_fee'] = $this->input('dev_realtime_fee', 0)/100;
        $data['brand_id'] = $this->user()->brand_id;
        return $data;
    }
}
