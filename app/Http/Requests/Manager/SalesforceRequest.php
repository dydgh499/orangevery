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

    public $file_keys = [
        'passbook_file',
        'contract_file',
        'bsin_lic_file',
        'id_file',
        'profile_file',
    ];
    public $image_keys = [
        'passbook_img',
        'contract_img',
        'bsin_lic_img',
        'id_img',
        'profile_img',
    ];
    public $integer_keys = [
        'sales_fee',
        'is_able_modify_mcht',
        'is_able_under_modify',
        'is_able_unlock_mcht',
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
            'resident_num' => 'nullable|string',
            'business_num' => 'nullable|string',
            'phone_num' => 'nullable|string',
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
        $params = array_merge($params, $this->getDocsParameters($this->integer_keys));
        $params = array_merge($params, $this->getDocsParameters($this->nullable_keys));
        
        $params['passbook_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['contract_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['bsin_lic_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['id_file']['description']    .= '(max-width:500px 이상은 리사이징)';
        return $params;
    }
    public function data()
    {
        $data = array_merge(
            $this->getParmasBaseKeyV2($this->integer_keys, 0), $this->getParmasBaseKeyV2($this->nullable_keys, null),
            $this->getParamsBaseFile($this->image_keys)
        );

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
