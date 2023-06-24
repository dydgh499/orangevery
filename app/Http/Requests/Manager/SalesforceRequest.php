<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class SalesforceRequest extends FormRequest
{
    use FormRequestTrait;
    public function __construct()
    {
        $this->keys = [
            'user_name',
            'nick_name',
            'level',
            'resident_num',
            'business_num',
            'acct_bank_nm',
            'acct_bank_cd',
            'acct_num',
            'acct_nm',
            'addr',
            'phone_num',
            'settle_tax_type',
            'settle_cycle',
            'settle_day',
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
            'nick_name' => 'required',
            'level'     => 'required',
            'resident_num' => 'required',
            'business_num' => 'required',
            'acct_bank_nm' => 'required',
            'acct_bank_cd' => 'required',
            'settle_tax_type' => 'required',
            'settle_cycle' => 'required',
            'settle_day' => 'required',
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
        $params['point_flag']['description']    .= '(1=사용, 0=미사용)';
        $params['stamp_flag']['description']    .= '(1=사용, 0=미사용)';
        $params['profile_img']['description']   .= '(max-width: 120px, 이상은 리사이징)';
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
        $data['brand_id'] = $this->user()->brand_id;
        $data['phone_num'] = $data['phone_num'] == '' ? 0 : $data['phone_num'];
        return $data;
    }
}
