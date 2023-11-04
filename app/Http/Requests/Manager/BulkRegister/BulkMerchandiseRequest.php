<?php

namespace App\Http\Requests\Manager\BulkRegister;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BulkMerchandiseRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'mcht_name',
        'user_name',
        'user_pw',
        'nick_name',
        'resident_num',
        'business_num',
        'sector',
        'addr',
        'phone_num',
        'acct_num',
        'acct_name',
        'acct_bank_name',
        'acct_bank_code',
    ];

    public function bodyParameters()
    {
        return $this->getDocsParameters($this->keys);
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
            '*.mcht_name' => 'required',
            '*.user_name' => 'required',
            '*.user_pw' => 'required',
            '*.nick_name' => 'required',
            '*.sector' => 'required',
            '*.acct_num' => 'required',
            '*.acct_name' => 'required',
            '*.acct_bank_name' => 'required',
            '*.acct_bank_code' => 'required',
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
                $data[$key] = isset($_datas[$i][$key]) ? $_datas[$i][$key] : '';
            }            
            $data['custom_id'] = isset($_datas[$i]['custom_id']) ? $_datas[$i]['custom_id'] : null;
            $data['sales0_id'] = isset($_datas[$i]['sales0_id']) ? $_datas[$i]['sales0_id'] : 0;
            $data['sales1_id'] = isset($_datas[$i]['sales1_id']) ? $_datas[$i]['sales1_id'] : 0;
            $data['sales2_id'] = isset($_datas[$i]['sales2_id']) ? $_datas[$i]['sales2_id'] : 0;
            $data['sales3_id'] = isset($_datas[$i]['sales3_id']) ? $_datas[$i]['sales3_id'] : 0;
            $data['sales4_id'] = isset($_datas[$i]['sales4_id']) ? $_datas[$i]['sales4_id'] : 0;
            $data['sales5_id'] = isset($_datas[$i]['sales5_id']) ? $_datas[$i]['sales5_id'] : 0;
            $data['hold_fee']  = isset($_datas[$i]['hold_fee']) && !empty($_datas[$i]['hold_fee']) ? $_datas[$i]['hold_fee']/100 : 0;
            $data['trx_fee']    = isset($_datas[$i]['trx_fee']) && !empty($_datas[$i]['trx_fee']) ?  $_datas[$i]['trx_fee']/100 : 0;
            $data['sales0_fee'] = isset($_datas[$i]['sales0_fee']) && !empty($_datas[$i]['sales0_fee']) ? $_datas[$i]['sales0_fee']/100 : 0;
            $data['sales1_fee'] = isset($_datas[$i]['sales1_fee']) && !empty($_datas[$i]['sales1_fee'])? $_datas[$i]['sales1_fee']/100 : 0;
            $data['sales2_fee'] = isset($_datas[$i]['sales2_fee']) && !empty($_datas[$i]['sales2_fee']) ? $_datas[$i]['sales2_fee']/100 : 0;
            $data['sales3_fee'] = isset($_datas[$i]['sales3_fee']) && !empty($_datas[$i]['sales3_fee']) ? $_datas[$i]['sales3_fee']/100 : 0;
            $data['sales4_fee'] = isset($_datas[$i]['sales4_fee']) && !empty($_datas[$i]['sales4_fee']) ? $_datas[$i]['sales4_fee']/100 : 0;
            $data['sales5_fee'] = isset($_datas[$i]['sales5_fee']) && !empty($_datas[$i]['sales5_fee']) ? $_datas[$i]['sales5_fee']/100 : 0;
            array_push($datas, $data);
        }
        return collect($datas);
    }
}
