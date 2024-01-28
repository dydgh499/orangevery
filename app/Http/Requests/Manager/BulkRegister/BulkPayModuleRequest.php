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
        'show_pay_view',
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
            '*.module_type' => 'required|numeric',
            '*.fin_trx_delay' => 'nullable|numeric',
            '*.terminal_id' => 'nullable|numeric',
            '*.settle_fee' => 'nullable|numeric',
            '*.comm_settle_fee' => 'nullable|numeric',
            '*.comm_settle_day' => 'nullable|numeric',
            '*.comm_calc_level' => 'nullable|numeric',
            '*.under_sales_amt' => 'nullable|numeric',
            '*.is_old_auth' => 'nullable|numeric',
            '*.show_pay_view' => 'nullable|numeric',
            '*.pay_dupe_least' => 'nullable|numeric',
            '*.abnormal_trans_limit' => 'nullable|numeric',
            '*.pay_dupe_limit' => 'nullable|numeric',
            '*.pay_year_limit' => 'nullable|numeric',
            '*.pay_month_limit' => 'nullable|numeric',
            '*.pay_day_limit' => 'nullable|numeric',
            '*.pay_single_limit' => 'nullable|numeric',
            '*.pg_id' => 'required|numeric',
            '*.ps_id' => 'required|numeric',
            '*.settle_type' => 'nullable|numeric',
            '*.mcht_id' => 'nullable|numeric',
            '*.installment' => 'required|numeric',
            '*.cxl_type' => 'nullable|numeric',
            '*.use_realtime_deposit' => 'nullable|numeric',
            '*.under_sales_limit' => 'nullable|numeric',
            '*.under_sales_type' => 'nullable|numeric',
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
            $data = array_merge($this->getParmasBaseKeyV3($_datas[$i], $this->integer_keys, 0), $this->getParmasBaseKeyV3($_datas[$i], $this->string_keys, ''));
            $data = array_merge($data, $this->getParmasBaseKeyV3($_datas[$i], $this->nullable_keys, null));
            array_push($datas, $data);
        }
        return collect($datas);
    }
}
