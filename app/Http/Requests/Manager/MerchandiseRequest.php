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
        'website_url',
        'email',
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
        if(isOperator($this))
            return true;
        else if(isSalesforce($this))
            return $this->user()->is_able_modify_mcht;        
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
            'mcht_name' => 'required',
            'addr'      => 'required',
            'acct_bank_name' => 'required',
            'acct_num'  => 'nullable|max:30',
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
