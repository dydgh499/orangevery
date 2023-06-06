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
            'custom_id',
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
            'custom_id' => $this->custom_id,
        ];
        $data['pv_options'] = json_encode($this->pv_options);
        return $data;
    }
}
