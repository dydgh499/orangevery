<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class ComplaintRequest extends FormRequest
{
    use FormRequestTrait;

    public function __construct()
    {
        $this->keys = [
            'mcht_id',
            'tid',
            'cust_nm',
            'appr_dt',
            'appr_num',
            'phone_num',
            'hand_cust_nm',
            'hand_phone_num',
            'issuer_id',
            'pg_id',
            'type',
            'entry_path',
            'is_deposit',
            'note',
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
            'mcht_id' => 'required',
            'tid' => 'required|string',
            'cust_nm' => 'required|string',
            'appr_dt' => 'required|date',
            'appr_num' => 'required|string',
            'phone_num' => 'required',
            'issuer_id' => 'required',
            'pg_id' => 'required',
            'type' => 'required',
            'entry_path' => 'required',
            'is_deposit' => 'required',
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
        $data = [];
        for ($i=0; $i < count($this->keys) ; $i++)
        {
            $key = $this->keys[$i];
            $data[$key] = $this->input($key, '');
        }
        $data['brand_id'] = $this->user()->brand_id;
        return $data;
    }
}
