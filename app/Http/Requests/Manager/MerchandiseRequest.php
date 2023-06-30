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
            'addr',
            'resident_num',
            'business_num',
            'acct_bank_name',
            'acct_bank_code',
            'acct_num',
            'acct_name',
            'phone_num',
            'enabled',
            'use_saleslip_prov',
            'use_saleslip_sell',
            'is_show_fee',
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
            'acct_bank_name' => 'required',
            'acct_bank_code' => 'required',
            'enabled' => 'required|boolean',
            'use_saleslip_prov' => 'required|boolean',
            'use_saleslip_sell' => 'required|boolean',
            'is_show_fee' => 'required|boolean',
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
        $this->merge(['enabled' => $this->convertToBoolean($this->input('enabled'))]);
        $this->merge(['use_saleslip_prov' => $this->convertToBoolean($this->input('use_saleslip_prov'))]);
        $this->merge(['use_saleslip_sell' => $this->convertToBoolean($this->input('use_saleslip_sell'))]);
        $this->merge(['is_show_fee' => $this->convertToBoolean($this->input('is_show_fee'))]);
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
        $data = [];
        for ($i=0; $i < count($this->keys) ; $i++)
        {
            $key = $this->keys[$i];
            $data[$key] = $this->input($key, '');
        }
        $data['custom_id'] = $this->input('custom_id', null);
        return $data;
    }
}
