<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class PayModuleRequest extends FormRequest
{
    use FormRequestTrait;

    public function __construct()
    {
        $this->keys = [
            'mcht_id',
            'pg_id',
            'ps_id',
            'settle_type',
            'terminal_id',
            'module_type',
            'api_key',
            'sub_key',
            'mid',
            'tid',
            'comm_settle_fee',
            'comm_settle_type',
            'comm_calc_level',
            'begin_dt',
            'ship_out_dt',
            'ship_out_stat',
            'is_old_auth',
            'use_saleslip_prov',
            'use_saleslip_sell',
            'installment',
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
            'pg_id' => 'required',
            'ps_id' => 'required',
            'settle_type' => 'required',
            'module_type' => 'required',
            'is_old_auth' => 'required',
            'use_saleslip_prov' => 'required',
            'use_saleslip_sell' => 'required',
            'installment' => 'required',
            'note' => 'required',
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
    public function data()
    {
        $data = [];
        for ($i=0; $i < count($this->keys) ; $i++)
        {
            $key = $this->keys[$i];
            $data[$key] = $this->input($key, '');
        }
        $data['brand_id'] = $this->user()->brand_id;
        $data['terminal_id'] = $data['terminal_id'] == null ? 0 : $data['terminal_id'];
        $data['begin_dt']    = $data['begin_dt'] == '' ? '1970-01-01' : $data['begin_dt'];
        $data['ship_out_dt'] = $data['ship_out_dt'] == '' ? '1970-01-01' : $data['ship_out_dt'];
        return $data;
    }
}
