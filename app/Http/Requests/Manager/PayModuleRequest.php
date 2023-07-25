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
            'settle_type',
            'terminal_id',
            'module_type',
            'api_key',
            'sub_key',
            'mid',
            'tid',
            'serial_num',
            'comm_settle_fee',
            'comm_settle_type',
            'comm_calc_level',
            'begin_dt',
            'ship_out_dt',
            'ship_out_stat',
            'is_old_auth',
            'pay_dupe_limit',
            'abnormal_trans_limit',
            'pay_year_limit',
            'pay_month_limit',
            'pay_day_limit',
            'pay_disable_s_tm',
            'pay_disable_e_tm',
            'show_pay_view',
            'installment',
            'note',
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
            'settle_type' => 'required',
            'module_type' => 'required',
            'is_old_auth' => 'required',
            'pay_dupe_limit' => 'required',
            'abnormal_trans_limit' => 'required',
            'pay_year_limit' => 'required',
            'pay_month_limit' => 'required',
            'pay_day_limit' => 'required',
            'show_pay_view' => 'required',
            'installment' => 'required',
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
        $data['brand_id'] = $this->user()->brand_id;
        $data['terminal_id'] = $data['terminal_id'] == null ? 0 : $data['terminal_id'];
        $data['begin_dt']    = $data['begin_dt'] == '' ? '1970-01-01' : $data['begin_dt'];
        $data['ship_out_dt'] = $data['ship_out_dt'] == '' ? '1970-01-01' : $data['ship_out_dt'];
        $data['filter_issuers'] = json_encode($this->filter_issuers);
        return $data;
    }
}
