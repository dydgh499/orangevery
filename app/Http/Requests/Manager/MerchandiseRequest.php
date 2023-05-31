<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class MerchandiseRequest extends FormRequest
{
    use FormRequestTrait;

    public function __construct()
    {
        $this->keys = [
            'user_name',
            'nick_name',
            'mcht_name',
            'trx_fee',
            'hold_fee',
            'addr',
            'passbook_file',
            'contract_file',
            'bsin_lic_file',
            'id_file',
            'resident_num',
            'business_num',
            'acct_bank_nm',
            'acct_bank_cd',
            'acct_num',
            'acct_nm',
            'phone_num',
            'pv_options',
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
            'user_name' => 'required',
            'mcht_name' => 'required',
            'addr'      => 'required',
            'resident_num' => 'required',
            'business_num' => 'required',
            'acct_bank_nm' => 'required',
            'acct_bank_cd' => 'required',
            'pv_options' => 'required',
            'trx_fee'   => 'required',
            'passbook_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'contract_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'bsin_lic_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'id_file'        => 'file|mimes:jpg,bmp,png,jpeg,webp',

        ];
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }

    protected function prepareForValidation()
    {
        if ($this->has('pv_options')) 
        {
            $pvOptions = $this->input('pv_options');    
            $pvOptions = $this->convertToBoolean($pvOptions);
            $this->merge(['pv_options' => $pvOptions]);
        }
    }

    protected function convertToBoolean($data)
    {
        if (is_array($data)) 
        {
            foreach ($data as $key => $value) {
                $data[$key] = $this->convertToBoolean($value);
            }
        } 
        else 
        {
            if ($data === 'true')
                $data = true;
            else if ($data === 'false')
                $data = false;
        }
        return $data;
    }

    public function bodyParameters()
    {
        $params = $this->getDocsParameters($this->keys);
        $params['passbook_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['contract_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['bsin_lic_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['id_file']['description']    .= '(max-width:500px 이상은 리사이징)';
        return $params;
    }
    public function data()
    {
        $data = [
            'brand_id'  => $this->user()->brand_id,
            'user_name' => $this->user_name,
            'mcht_name' => $this->mcht_name,
            'nick_name' => $this->input('nick_name', ''),
            'addr' => $this->addr,
            'resident_num' => $this->resident_num,
            'business_num' => $this->business_num,
            'sector'    => $this->sector,
            'acct_bank_nm' => $this->acct_bank_nm,
            'acct_bank_cd' => $this->acct_bank_cd,
            'acct_num' => $this->input('acct_num', ''),
            'acct_nm' => $this->input('acct_nm', ''),
            'sales0_id' => $this->input('sales0_id', 0),
            'sales1_id' => $this->input('sales1_id', 0),
            'sales2_id' => $this->input('sales2_id', 0),
            'sales3_id' => $this->input('sales3_id', 0),
            'sales4_id' => $this->input('sales4_id', 0),
            'sales5_id' => $this->input('sales5_id', 0),
            'hold_fee'  => $this->input('hold_fee', 0)/100,
            'trx_fee'  => $this->input('trx_fee', 0)/100,
            'sales0_fee' => $this->input('sales0_fee', 0)/100,
            'sales1_fee' => $this->input('sales1_fee', 0)/100,
            'sales2_fee' => $this->input('sales2_fee', 0)/100,
            'sales3_fee' => $this->input('sales3_fee', 0)/100,
            'sales4_fee' => $this->input('sales4_fee', 0)/100,
            'sales5_fee' => $this->input('sales5_fee', 0)/100,
        ];        
        $data['sales0_id'] = $data['sales0_id'] == null ? 0 : $data['sales0_id'];
        $data['sales1_id'] = $data['sales1_id'] == null ? 0 : $data['sales1_id'];
        $data['sales2_id'] = $data['sales2_id'] == null ? 0 : $data['sales2_id'];
        $data['sales3_id'] = $data['sales3_id'] == null ? 0 : $data['sales3_id'];
        $data['sales4_id'] = $data['sales4_id'] == null ? 0 : $data['sales4_id'];
        $data['sales5_id'] = $data['sales5_id'] == null ? 0 : $data['sales5_id'];
        $data['pv_options'] = json_encode($this->pv_options, true);
        return $data;
    }
}
