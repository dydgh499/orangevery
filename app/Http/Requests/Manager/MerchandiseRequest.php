<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class MerchandiseRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'user_name',
        'nick_name',
        'mcht_name',
        'addr',
        'sector',
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
        'is_hide_account',
        'use_noti',
        'use_pay_verification_mobile',
        'use_multiple_hand_pay',
        'use_regular_card',
        'use_collect_withdraw',
        'tax_category_type',
        'collect_withdraw_fee',
        'withdraw_fee',
        'is_show_fee',
        'note',
        'passbook_img',
        'contract_img',
        'bsin_lic_img',
        'id_img',
    ];
    public $file_keys = [
        'passbook_file',
        'contract_file',
        'bsin_lic_file',
        'id_file',
        'profile_file',

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
            'mcht_name' => 'required',
            'addr'      => 'required',
            'acct_bank_name' => 'required',
            'enabled' => 'required|boolean',
            'use_saleslip_prov' => 'required|boolean',
            'use_saleslip_sell' => 'required|boolean',
            'is_hide_account'   => 'required|boolean',
            'collect_withdraw_fee' => 'nullable|numeric',
            'tax_category_type' => 'nullable|numeric',
            'withdraw_fee' => 'nullable|numeric',
            'is_show_fee' => 'required|boolean',
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
        $this->merge(['enabled' => $this->convertToBoolean($this->input('enabled'))]);
        $this->merge(['use_noti' => $this->convertToBoolean($this->input('use_noti'))]);
        $this->merge(['use_saleslip_prov' => $this->convertToBoolean($this->input('use_saleslip_prov'))]);
        $this->merge(['use_saleslip_sell' => $this->convertToBoolean($this->input('use_saleslip_sell'))]);
        $this->merge(['is_hide_account' => $this->convertToBoolean($this->input('is_hide_account', 0))]);       
        $this->merge(['is_show_fee'     => $this->convertToBoolean($this->input('is_show_fee'))]);
        $this->merge(['use_regular_card' => $this->convertToBoolean($this->input('use_regular_card'))]);
        $this->merge(['use_collect_withdraw' => $this->convertToBoolean($this->input('use_collect_withdraw'))]);
        $this->merge(['use_pay_verification_mobile' => $this->convertToBoolean($this->input('use_pay_verification_mobile', 0))]);
        $this->merge(['use_multiple_hand_pay' => $this->convertToBoolean($this->input('use_multiple_hand_pay', 0))]);
    }

    public function bodyParameters()
    {
        $params = array_merge($this->getDocsParameters($this->keys), $this->getDocsParameters($this->file_keys));
        $params['passbook_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['contract_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['bsin_lic_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['id_file']['description']    .= '(max-width:500px 이상은 리사이징)';
        return $params;
    }
    public function data()
    {
        $data = $this->getParmasBaseKey();
        $data['brand_id'] = $this->user()->brand_id;
        $data['custom_id'] = $this->input('custom_id', null);
        if($data['acct_bank_code'] == '')
            $data['acct_bank_code'] = "000";
        if($this->has('profile_img'))
            $data['profile_img'] = $this->profile_img;
        return $data;
    }
}
