<?php

namespace App\Http\Requests\Manager\BulkRegister;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BulkPayModuleRequest extends FormRequest
{
    use FormRequestTrait;

    public function __construct()
    {
        $this->keys = [
            'mcht_id',
            'pg_id',
            'ps_id',
            'settle_type',
            'module_type',
            'installment',
            'note',
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
            for ($j=0; $j < count($this->keys) ; $j++) 
            {
                $key = $this->keys[$j];
                $data[$key] = $_datas[$i][$key];
            }
            $data['api_key'] = isset($_datas[$i]['api_key']) ? $_datas[$i]['api_key'] : '';
            $data['sub_key'] = isset($_datas[$i]['sub_key']) ? $_datas[$i]['sub_key'] : '';
            $data['mid'] = isset($_datas[$i]['mid']) ? $_datas[$i]['mid'] : '';
            $data['tid'] = isset($_datas[$i]['tid']) ? $_datas[$i]['tid'] : '';

            if($data['module_type'] == 0)
            {
                $data['terminal_id'] = isset($_datas[$i]['terminal_id']) ? $_datas[$i]['terminal_id'] : 0;
                $data['begin_dt'] = isset($_datas[$i]['begin_dt']) ? $_datas[$i]['begin_dt'] : null;
                $data['ship_out_dt'] = isset($_datas[$i]['ship_out_dt']) ? $_datas[$i]['ship_out_dt'] : null;
                $data['ship_out_stat'] = isset($_datas[$i]['ship_out_stat']) ? $_datas[$i]['ship_out_stat'] : null;
                $data['comm_settle_fee'] = isset($_datas[$i]['comm_settle_fee']) ? $_datas[$i]['comm_settle_fee'] : 0;
                $data['comm_settle_type'] = isset($_datas[$i]['comm_settle_type']) ? $_datas[$i]['comm_settle_type'] : 0;
                $data['comm_calc_level'] = isset($_datas[$i]['comm_calc_level']) ? $_datas[$i]['comm_calc_level'] : 0;
                $data['under_sales_amt'] = isset($_datas[$i]['under_sales_amt']) ? $_datas[$i]['under_sales_amt'] : 0;
                $data['serial_num'] = isset($_datas[$i]['serial_num']) ? $_datas[$i]['serial_num'] : '';        
            }
            else if($data['module_type'] == 1 || $data['module_type'] == 5)
            {
                $data['is_old_auth'] = isset($_datas[$i]['is_old_auth']) ? $_datas[$i]['is_old_auth'] : 0;
            }

            $data['show_pay_view'] = isset($_datas[$i]['show_pay_view']) ? $_datas[$i]['show_pay_view'] : true;
            $data['abnormal_trans_limit'] = isset($_datas[$i]['abnormal_trans_limit']) ? $_datas[$i]['abnormal_trans_limit'] : 0;
            $data['pay_dupe_limit'] = isset($_datas[$i]['pay_dupe_limit']) ? $_datas[$i]['pay_dupe_limit'] : 0;
            $data['pay_year_limit'] = isset($_datas[$i]['pay_year_limit']) ? $_datas[$i]['pay_year_limit'] : 0;
            $data['pay_month_limit'] = isset($_datas[$i]['pay_month_limit']) ? $_datas[$i]['pay_month_limit'] : 0;
            $data['pay_day_limit'] = isset($_datas[$i]['pay_day_limit']) ? $_datas[$i]['pay_day_limit'] : 0;

            $data['pay_disable_s_tm'] = isset($_datas[$i]['pay_disable_s_tm']) ? $_datas[$i]['pay_disable_s_tm'] : null;
            $data['pay_disable_e_tm'] = isset($_datas[$i]['pay_disable_e_tm']) ? $_datas[$i]['pay_disable_e_tm'] : null;
            array_push($datas, $data);
        }
        return collect($datas);
    }
}
