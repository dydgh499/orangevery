<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class TransactionRequest extends FormRequest
{
    use FormRequestTrait;
    public function __construct()
    {
        $this->keys = [
            'mcht_id',
            'sales5_id', 'sales5_fee',
            'sales4_id', 'sales4_fee',
            'sales3_id', 'sales3_fee',
            'sales2_id', 'sales2_fee',
            'sales1_id', 'sales1_fee',
            'sales0_id', 'sales0_fee',
            'custom_id', 'mcht_fee', 'hold_fee',
            'mid','tid',
            'module_type',
            'pg_id', 'pmod_id', 'ps_id',
            'terminal_id',
            'ps_fee',
            'mcht_settle_fee', 'mcht_settle_type',
            'trx_dt',
            'trx_tm',
            'amount',
            'ord_num',
            'trx_id',
            'card_num',
            'installment',
            'issuer',
            'acquirer',
            'appr_num',
            'cxl_dt', 
            'cxl_tm', 
            'ori_trx_id',   //
            'buyer_name',   //
            'buyer_phone',  //
            'item_name',    //
        ];
    }

    public function authorize()
    {
        return $this->user()->tokenCan(10) ? true : false;
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
            'sales5_fee' => 'required',
            'sales4_fee' => 'required',
            'sales3_fee' => 'required',
            'sales2_fee' => 'required',
            'sales1_fee' => 'required',
            'sales0_fee' => 'required',
            'mcht_fee' => 'required', 'hold_fee' => 'required',
            'module_type' => 'required',
            'pg_id' => 'required', 'pmod_id' => 'required', 'ps_id' => 'required', 'ps_fee' => 'required',
            'mcht_settle_fee' => 'required', 'mcht_settle_type'=> 'required',
            'trx_dt' => 'required', 'trx_tm' => 'required',
            'amount' => 'required',
            'ord_num' => 'required',
            'trx_id' => 'required',
            'card_num' => 'required',
            'installment' => 'required',
            'issuer', 'acquirer' => 'required',
            'appr_num' => 'required',
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
        $data['amount'] = abs($data['amount']);
        $data['mcht_settle_fee']  = abs($data['mcht_settle_fee']);
        
        $data['sales0_id'] = $this->input('sales0_id', null);
        $data['sales1_id'] = $this->input('sales1_id', null);
        $data['sales2_id'] = $this->input('sales2_id', null);
        $data['sales3_id'] = $this->input('sales3_id', null);
        $data['sales4_id'] = $this->input('sales4_id', null);
        $data['sales5_id'] = $this->input('sales5_id', null);
        $data['custom_id'] = $this->input('custom_id', null);
        
        $data['ps_fee']  = $this->input('ps_fee', 0)/100;
        $data['hold_fee']  = $this->input('hold_fee', 0)/100;
        $data['mcht_fee']    = $this->input('mcht_fee', 0)/100;
        $data['sales0_fee'] = $this->input('sales0_fee', 0)/100;
        $data['sales1_fee'] = $this->input('sales1_fee', 0)/100;
        $data['sales2_fee'] = $this->input('sales2_fee', 0)/100;
        $data['sales3_fee'] = $this->input('sales3_fee', 0)/100;
        $data['sales4_fee'] = $this->input('sales4_fee', 0)/100;
        $data['sales5_fee'] = $this->input('sales5_fee', 0)/100;

        $data['brand_id'] = $this->user()->brand_id;
        $data['cxl_dt'] = $data['cxl_dt'] == '' ? null : $data['cxl_dt'];
        $data['cxl_tm'] = $data['cxl_tm'] == '' ? null : $data['cxl_tm'];
        $data['is_cancel'] = $data['cxl_dt'] == null ? false : true;
        $data['ori_trx_id'] = $data['cxl_dt'] == null ? null : $data['trx_id'];
        $data['terminal_id'] = $data['terminal_id'] == '' ? null : $data['terminal_id'];

        if($data['is_cancel'])
        {
            $data['amount'] *= -1;
            $data['mcht_settle_fee'] *= -1;
        }
        return $data;
    }
}
