<?php

namespace App\Http\Requests\Manager\Merchandise;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;
use App\Http\Controllers\Ablilty\Ablilty;

class PayModuleRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'mcht_id',
        'pg_id',
        'ps_id',
        'settle_fee',
        'settle_type',
        'module_type',
        'api_key',
        'sub_key',
        'mid',
        'p_mid',
        'tid',
        'cxl_type',
        'serial_num',
        'comm_settle_fee',
        'comm_settle_type',
        'comm_calc_level',
        'abnormal_trans_limit',
        'pay_dupe_limit',
        'pay_dupe_least',
        'pay_year_limit',
        'pay_month_limit',
        'pay_day_limit',
        'pay_single_limit',
        'installment',
        'note',
    ];
    public $boolean_keys = [
        'is_old_auth',
        'use_realtime_deposit',
        'is_able_bill_key',
    ];
    public $nullable_keys = [
        'contract_s_dt',
        'contract_e_dt',
        'begin_dt',
        'ship_out_dt',
        'fin_id',
        'fin_trx_delay',
        'pay_disable_s_tm',
        'pay_disable_e_tm',
    ];
    
    public $integer_keys = [
        'under_sales_type',
        'under_sales_limit',
        'under_sales_amt',
        'terminal_id',
        'ship_out_stat',
        'comm_settle_day',
        'payment_term_min',
        'pay_window_secure_level',
        'pay_window_extend_hour',
    ];

    public function authorize()
    {
        if(Ablilty::isOperator($this))
            return true;
        else if(Ablilty::isSalesforce($this))
        {
            if($this->user()->brand_id === 30)
                return true;
            else
                return $this->user()->is_able_modify_mcht;        
        }
        else
            return false;
    }

    public function rules()
    {
        $sub = [
            'mcht_id' => 'required',
            'pg_id' => 'required',
            'ps_id' => 'required',
            'settle_fee' => 'required',
            'settle_type' => 'required',
            'module_type' => 'required',
            'abnormal_trans_limit' => 'required',
            'pay_dupe_limit' => 'required',
            'pay_dupe_least' => 'required',
            'pay_year_limit' => 'required',
            'pay_month_limit' => 'required',
            'pay_day_limit' => 'required',
            'pay_single_limit' => 'required',
            'installment' => 'required',
            'note' => 'required',
            'is_old_auth' => 'required',
            'use_realtime_deposit' => 'required',
            'pay_window_secure_level' => 'required',
            'comm_settle_fee' => 'required|integer',
            'under_sales_amt' => 'nullable|integer',
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
        $data['filter_issuers'] = json_encode($this->filter_issuers);
        if($this->fin_trx_delay !== null)
            $data['fin_trx_delay'] = $this->fin_trx_delay;
        return $data;
    }
}
