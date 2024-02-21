<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

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
        'passbook_img',
        'contract_img',
        'bsin_lic_img',
        'id_img',
    ];

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
            'user_name' => 'required',
            'sales_name' => 'required',
            'view_type' => 'required',
            'is_able_modify_mcht' => 'required',
            'level'     => 'required',
            'acct_bank_name' => 'required',
            'settle_tax_type' => 'required',
            'settle_cycle' => 'required',
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

    protected function prepareForValidation()
    {
        if ($this->has('view_type')) 
        {
            $this->merge(['view_type' => $this->convertToBoolean($this->input('view_type'))]);
        }
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
            $data[$key] = $this->input($key, null);
        }
        $data['brand_id'] = $this->user()->brand_id;
        $data['phone_num'] = $data['phone_num'] == '' ? 0 : $data['phone_num'];
        if($data['acct_bank_code'] == '')
            $data['acct_bank_code'] = "000";
        if($this->has('profile_img'))
            $data['profile_img'] = $this->profile_img;
        return $data;
    }
}
