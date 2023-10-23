<?php

namespace App\Http\Requests\Manager\BulkRegister;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BulkSalesforceRequest extends FormRequest
{
    use FormRequestTrait;
    public function __construct()
    {
        $this->keys = [
            'user_name',
            'user_pw',
            'sales_name',
            'nick_name',
            'level'    ,
            'resident_num',
            'business_num',
            'sector',
            'acct_num',
            'acct_name',
            'acct_bank_name',
            'acct_bank_code',
            'settle_tax_type',
            'settle_cycle',
            'settle_day',
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
            '*.user_name' => 'required',
            '*.user_pw' => 'required',
            '*.nick_name' => 'required',
            '*.level'     => 'required',
            '*.resident_num' => 'required',
            '*.business_num' => 'required',
            '*.sector' => 'required',
            '*.acct_num' => 'required',
            '*.acct_name' => 'required',
            '*.acct_bank_name' => 'required',
            '*.acct_bank_code' => 'required',
            '*.settle_tax_type' => 'required',
            '*.settle_cycle' => 'required',
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
            array_push($datas, $data);
        }
        return collect($datas);
    }
}
