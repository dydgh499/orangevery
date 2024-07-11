<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;
use App\Http\Controllers\Ablilty\Ablilty;

class SalesforceRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'user_name',
        'nick_name',
        'sales_name',
        'view_type',
        'is_able_modify_mcht',
        'level',
        'resident_num',
        'business_num',
        'acct_bank_name',
        'acct_bank_code',
        'acct_num',
        'acct_name',
        'addr',
        'phone_num',
        'settle_tax_type',
        'settle_cycle',
        'settle_day',
        'note',
    ];
    public $integer_keys = [
        'sales_fee',
        'is_able_under_modify',
    ];
    public $nullable_keys = [
        'parent_id'
    ];

    public function authorize()
    {
        if(Ablilty::isOperator($this))
            return true;
        else if(Ablilty::isSalesforce($this))
        {
            if($this->user()->brand_id === 30)
                return true;
            else
                return $this->user()->is_able_modify_mcht;        
        }
        else
            return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $sub = [
            'user_name' => 'required',
            'sales_name' => 'required',
            'view_type' => 'required',
            'is_able_modify_mcht' => 'required',
            'level'     => 'required',
            'settle_tax_type' => 'required',
            'settle_cycle' => 'required',
            'sales_fee' => 'nullable|numeric|max:10',
            'resident_num' => 'nullable|numeric',
            'business_num' => 'nullable|numeric',
            'phone_num' => 'nullable|numeric',
            'passbook_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp,pdf',
            'contract_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp,pdf',
            'bsin_lic_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp,pdf',
            'id_file'        => 'file|mimes:jpg,bmp,png,jpeg,webp,pdf',
            'profile_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp',
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
        $data = array_merge($this->getParmasBaseKeyV2($this->integer_keys, 0), $this->getParmasBaseKeyV2($this->nullable_keys, null));
        for ($i=0; $i < count($this->keys) ; $i++)
        {
            $key = $this->keys[$i];
            $data[$key] = $this->input($key, null);
        }
        $data['brand_id'] = $this->user()->brand_id;
        $data['phone_num'] = $data['phone_num'] == '' ? 0 : $data['phone_num'];
        if($data['acct_bank_code'] == '')
            $data['acct_bank_code'] = "000";
        return $data;
    }
}
