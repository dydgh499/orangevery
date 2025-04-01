<?php

namespace App\Http\Requests\Manager\BulkRegister;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BulkSalesforceRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'user_name',
        'user_pw',
        'addr',
        'phone_num',
        'sales_name',
        'nick_name',
        'level',
        'resident_num',
        'business_num',
        'sector',
        'acct_num',
        'acct_name',
        'acct_bank_name',
        'acct_bank_code',
        'corp_registration_num',
    ];
    public $integer_keys = [
        'settle_tax_type',
        'settle_cycle',
        'view_type',
        'business_type',
    ];
    public $nullable_keys = [
        'settle_day',
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
            '*.user_name' => 'required',
            '*.user_pw' => 'required',
            '*.level'     => 'required',
            '*.resident_num' => 'required',
            '*.acct_num' => 'required',
            '*.acct_name' => 'required',
            '*.acct_bank_name' => 'required',
            '*.acct_bank_code' => 'required',
            '*.settle_tax_type' => 'required',
            '*.settle_cycle' => 'required',
            '*.view_type'   => 'required|integer',
        ];
        return $sub;
    }
    
    public function bodyParameters()
    {
        $params = $this->getDocsParameters($this->keys);
        return $params;
    }

    public function data()
    {
        $datas = [];
        $_datas = $this->all();
        for ($i=0; $i < count($_datas) ; $i++)
        {
            $data = array_merge(
                $this->getParmasBaseKeyV3($_datas[$i], $this->keys, ''),
                $this->getParmasBaseKeyV3($_datas[$i], $this->integer_keys, 0),
                $this->getParmasBaseKeyV3($_datas[$i], $this->nullable_keys, null)
            );
            $datas[] = $data;
        }
        return collect($datas);
    }
}
