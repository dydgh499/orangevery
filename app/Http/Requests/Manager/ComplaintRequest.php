<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class ComplaintRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'mcht_id',
        'tid',
        'cust_name',
        'appr_dt',
        'appr_num',
        'phone_num',
        'hand_cust_name',
        'hand_phone_num',
        'issuer',
        'pg_id',
        'type',
        'entry_path',
        'is_deposit',
        'complaint_status',
        'note',
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
            'mcht_id' => 'required',
            'tid' => 'required|string',
            'cust_name' => 'required|string',
            'appr_dt' => 'required|date',
            'appr_num' => 'required|string',
            'phone_num' => 'required',
            'issuer' => 'required',
            'pg_id' => 'required',
            'type' => 'required',
            'entry_path' => 'required',
            'is_deposit' => 'required',
            'complaint_status' => 'required',
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

    protected function prepareForValidation()
    {
        if ($this->has('is_deposit')) 
        {
            $is_deposit = $this->input('is_deposit');    
            $is_deposit = $this->convertToBoolean($is_deposit);
            $this->merge(['is_deposit' => $is_deposit]);
        }
    }

    public function data()
    {
        $data = $this->getParmasBaseKey();
        if(strpos($data['appr_dt'], 'T') !== false)
            $data['appr_dt'] = explode('T', $data['appr_dt'])[0];

        $data['brand_id'] = $this->user()->brand_id;
        return $data;
    }
}
