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
    public $integer_keys = [
        'use_collect_withdraw',
        'use_regular_card',
        'hold_fee',
        'trx_fee',
        'sales0_fee',
        'sales1_fee',
        'sales2_fee',
        'sales3_fee',
        'sales4_fee',
        'sales5_fee',        
        'collect_withdraw_fee',
        'withdraw_fee',
        'tax_category_type',
    ];
    public $nullable_keys = [
        'custom_id',
        'sales0_id',
        'sales1_id',
        'sales2_id',
        'sales3_id',
        'sales4_id',
        'sales5_id',
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
            '*.use_collect_withdraw' => 'nullable|numeric',
            '*.use_regular_card' => 'nullable|numeric',
            '*.hold_fee' => 'nullable|numeric',
            '*.trx_fee' => 'nullable|numeric',
            '*.sales0_fee' => 'nullable|numeric',
            '*.sales1_fee' => 'nullable|numeric',
            '*.sales2_fee' => 'nullable|numeric',
            '*.sales3_fee' => 'nullable|numeric',
            '*.sales4_fee' => 'nullable|numeric',
            '*.sales5_fee' => 'nullable|numeric',
            '*.collect_withdraw_fee' => 'nullable|numeric',
            '*.withdraw_fee' => 'nullable|numeric',
            '*.tax_category_type' => 'nullable|numeric',
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
            for ($j=0; $j < count($this->integer_keys); $j++) 
            {
                $key = $this->integer_keys[$j];
                $data[$key] = isset($_datas[$i][$key]) ? $_datas[$i][$key] : 0;
            }
            for ($j=0; $j < count($this->nullable_keys); $j++) 
            {
                $key = $this->nullable_keys[$j];
                $data[$key] = isset($_datas[$i][$key]) ? $_datas[$i][$key] : null;
            }
            $data['hold_fee']   /= 100; 
            $data['trx_fee']    /= 100; 
            $data['sales0_fee']  /= 100; 
            $data['sales1_fee']  /= 100; 
            $data['sales2_fee']  /= 100; 
            $data['sales3_fee']  /= 100; 
            $data['sales4_fee']  /= 100; 
            $data['sales5_fee']  /= 100; 
            array_push($datas, $data);
        }
        return collect($datas);
    }
}
