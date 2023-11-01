<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class PayModuleRequest extends FormRequest
{
    use FormRequestTrait;

    public function __construct()
    {
        $this->keys = [
            'mcht_id',
            'pg_id',
            'ps_id',
            'settle_fee',
            'settle_type',
            'terminal_id',
            'module_type',
            'api_key',
            'sub_key',
            'mid',
            'tid',
            'cxl_type',
            'serial_num',
            'comm_settle_fee',
            'comm_settle_day',
            'comm_settle_type',
            'comm_calc_level',
            'ship_out_stat',
            'abnormal_trans_limit',
            'pay_dupe_limit',
            'pay_dupe_least',
            'under_sales_type',
            'under_sales_amt',
            'under_sales_limit',
            'pay_year_limit',
            'pay_month_limit',
            'pay_day_limit',
            'pay_single_limit',
            'pay_disable_s_tm',
            'pay_disable_e_tm',
            'installment',
            'note',
        ];
        $this->boolean_keys = [
            'is_old_auth',
            'show_pay_view',
            'use_realtime_deposit',
        ];
        $this->nullable_keys = [
            'contract_s_dt',
            'contract_e_dt',
            'begin_dt',
            'ship_out_dt',
            'fin_id',
            'fin_trx_delay',
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
            'show_pay_view' => 'required',
            'comm_settle_fee' => 'required|numeric',
        ];
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }

    protected function prepareForValidation()
    {
        for ($i=0; $i <count($this->boolean_keys) ; $i++) 
        { 
            $this->merge([$this->boolean_keys[$i] => $this->convertToBoolean($this->input($this->boolean_keys[$i]))]);
        }
    }

    public function bodyParameters()
    {
        $params = $this->getDocsParameters($this->keys);
        return $params;
    }
    public function data()
    {
        $data = array_merge($this->getParmasBaseKeyV2($this->nullable_keys, null), $this->getParmasBaseKeyV2($this->boolean_keys, false));
        $data = array_merge($this->getParmasBaseKey(), $data);
        $data['brand_id'] = $this->user()->brand_id;
        $data['under_sales_type'] = $data['under_sales_type'] == null ? 0 : $data['under_sales_type'];
        $data['under_sales_limit'] = $data['under_sales_limit'] == '' ? 0 : $data['under_sales_limit'];
        $data['under_sales_amt'] = $data['under_sales_amt'] == '' ? 0 : $data['under_sales_amt'];
        $data['terminal_id'] = $data['terminal_id'] == null ? 0 : $data['terminal_id'];
        $data['ship_out_stat'] = $data['ship_out_stat'] == null ? 0 : $data['ship_out_stat'];        
        $data['filter_issuers'] = json_encode($this->filter_issuers);
        $data['comm_settle_day'] = $data['comm_settle_day'] == '' ? 0 : $data['comm_settle_day'];
        return $data;
    }
}
