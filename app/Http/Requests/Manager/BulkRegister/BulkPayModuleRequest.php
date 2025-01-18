<?php

namespace App\Http\Requests\Manager\BulkRegister;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BulkPayModuleRequest extends FormRequest
{
    use FormRequestTrait;
    public $integer_keys = [
        'module_type',
        'fin_trx_delay',
        'terminal_id',
        'settle_fee',
        'comm_settle_fee',
        'comm_settle_day',
        'comm_calc_level',
        'under_sales_amt',
        'is_old_auth',
        'pay_window_secure_level',
        'pay_dupe_least',
        'abnormal_trans_limit',
        'pay_dupe_limit',
        'pay_year_limit',
        'pay_month_limit',
        'pay_day_limit',
        'pay_single_limit',
        'pg_id',
        'ps_id',
        'settle_type',
        'mcht_id',
        'installment',
        'cxl_type',
        'use_realtime_deposit',
        'under_sales_limit',
        'under_sales_type',
    ];
    public $string_keys = [
        'api_key',
        'sub_key',
        'pay_key',
        'sign_key',
        'p_mid',
        'mid',
        'tid',
        'serial_num',
        'note',
    ];
    public $nullable_keys = [
        'fin_id',
        'begin_dt',
        'ship_out_dt',
        'contract_s_dt',
        'contract_e_dt',
        'ship_out_stat',
        'pay_disable_s_tm',
        'pay_disable_e_tm',
        'comm_settle_type',
    ];

    public function authorize()
    {
        return $this->user()->tokenCan(35) ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $sub = [
            '*' => 'required|array',
            '*.mcht_id' => 'required',
            '*.settle_type' => 'required',
            '*.begin_dt' => 'date|nullable',
            '*.ship_out_dt' => 'date|nullable',
            '*.pay_disable_s_tm' => 'nullable|date_format:H:i:s',
            '*.pay_disable_e_tm' => 'nullable|date_format:H:i:s',
            '*.module_type' => 'required|integer',
            '*.fin_trx_delay' => 'nullable|integer',
            '*.terminal_id' => 'nullable|integer',
            '*.settle_fee' => 'nullable|integer',
            '*.comm_settle_fee' => 'nullable|integer',
            '*.comm_settle_day' => 'nullable|integer',
            '*.comm_calc_level' => 'nullable|integer',
            '*.under_sales_amt' => 'nullable|integer',
            '*.is_old_auth' => 'nullable|integer',
            '*.pay_window_secure_level' => 'nullable|integer',
            '*.pay_dupe_least' => 'nullable|integer',
            '*.abnormal_trans_limit' => 'nullable|integer',
            '*.pay_dupe_limit' => 'nullable|integer',
            '*.pay_year_limit' => 'nullable|integer',
            '*.pay_month_limit' => 'nullable|integer',
            '*.pay_day_limit' => 'nullable|integer',
            '*.pay_single_limit' => 'nullable|integer',
            '*.pg_id' => 'required|integer',
            '*.ps_id' => 'required|integer',
            '*.settle_type' => 'nullable|integer',
            '*.mcht_id' => 'nullable|integer',
            '*.installment' => 'required|integer',
            '*.cxl_type' => 'nullable|integer',
            '*.use_realtime_deposit' => 'nullable|integer',
            '*.under_sales_limit' => 'nullable|integer',
            '*.under_sales_type' => 'nullable|integer',
        ];
        return $sub;
    }
    
    public function bodyParameters()
    {
        return array_merge($this->getDocsParameters($this->integer_keys), $this->getDocsParameters($this->string_keys), $this->getDocsParameters($this->nullable_keys));
    }

    public function data()
    {
        $datas = [];
        $_datas = $this->all();
        for ($i=0; $i < count($_datas) ; $i++)
        { 
            $data = array_merge(
                $this->getParmasBaseKeyV3($_datas[$i], $this->integer_keys, 0), 
                $this->getParmasBaseKeyV3($_datas[$i], $this->string_keys, ''),
                $this->getParmasBaseKeyV3($_datas[$i], $this->nullable_keys, null)
            );
            $data['payment_term_min'] = 1;
            $data['pay_window_extend_hour'] = 1;
            array_push($datas, $data);
        }
        return collect($datas);
    }
}
