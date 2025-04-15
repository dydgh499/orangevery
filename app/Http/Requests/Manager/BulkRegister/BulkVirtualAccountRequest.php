<?php

namespace App\Http\Requests\Manager\BulkRegister;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BulkVirtualAccountRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'account_name',
    ];
    public $integer_keys = [
        'user_id',
        'fin_id',
        'fin_trx_delay',
        'withdraw_type',
        'withdraw_fee',
        'withdraw_limit_type',
        'withdraw_business_limit',
        'withdraw_holiday_limit',
    ];
    public function bodyParameters()
    {
        return array_merge($this->getDocsParameters($this->integer_keys), $this->getDocsParameters($this->keys));
    }

    public function authorize()
    {
        return $this->user()->tokenCan(35) ? true : false;
    }

    public function rules()
    {
        $sub = [
            '*' => 'required|array',
            '*.account_name' => 'required',
            '*.user_id' => 'required',
            '*.fin_id' => 'required',
            '*.fin_trx_delay' => 'required',
            '*.withdraw_type' => 'required',
            '*.withdraw_fee' => 'required',
            '*.withdraw_limit_type' => 'required',
        ];
        return $sub;
    }
    
    public function data()
    {
        $datas = [];
        $_datas = $this->all();
        for ($i=0; $i < count($_datas) ; $i++)
        {
            $data = array_merge(
                $this->getParmasBaseKeyV3($_datas[$i], $this->integer_keys, 0), 
                $this->getParmasBaseKeyV3($_datas[$i], $this->keys, '')
            );
            $datas[] = $data;
        }
        return collect($datas);
    }
}
