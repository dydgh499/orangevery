<?php

namespace App\Http\Requests\Manager\BulkRegister;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BulkPayModuleRequest extends FormRequest
{
    use FormRequestTrait;

    public function __construct()
    {
        $this->integer_keys = [
            'terminal_id',
            'comm_settle_fee',
            'comm_settle_day',
            'comm_calc_level',
            'under_sales_amt',
            'is_old_auth',
            'show_pay_view',
            'abnormal_trans_limit',
            'pay_dupe_limit',
            'pay_year_limit',
            'pay_month_limit',
            'pay_day_limit',
            'pg_id',
            'ps_id',
            'settle_type',
            'module_type',
            'mcht_id',
            'installment',
        ];
        $this->string_keys = [
            'api_key',
            'sub_key',
            'mid',
            'tid',
            'serial_num',
            'note',
        ];
        $this->nullable_keys = [
            'begin_dt',
            'ship_out_dt',
            'ship_out_stat',
            'pay_disable_s_tm',
            'pay_disable_e_tm',
        ];
    }

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
            '*.pg_id' => 'required',
            '*.ps_id' => 'required',
            '*.settle_type' => 'required',
            '*.module_type' => 'required',
            '*.installment' => 'required',
            '*.begin_dt' => 'date|nullable',
            '*.ship_out_dt' => 'date|nullable',
        ];
        return $sub;
    }
    
    public function data()
    {
        $datas = [];
        $_datas = $this->all();
        for ($i=0; $i < count($_datas) ; $i++)
        { 
            $data = [];
            for ($j=0; $j < count($this->integer_keys) ; $j++) 
            {
                $key = $this->integer_keys[$j];
                $data[$key] = isset($_datas[$i][$key]) ? $_datas[$i][$key] : 0;
            }
            for ($j=0; $j < count($this->string_keys) ; $j++) 
            {
                $key = $this->string_keys[$j];
                $data[$key] = isset($_datas[$i][$key]) ? $_datas[$i][$key] : '';
            }
            for ($j=0; $j < count($this->nullable_keys) ; $j++) 
            {
                $key = $this->nullable_keys[$j];
                $data[$key] = isset($_datas[$i][$key]) ? $_datas[$i][$key] : null;
            }
            array_push($datas, $data);
        }
        return collect($datas);
    }
}
