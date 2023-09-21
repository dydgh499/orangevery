<?php

namespace App\Http\Requests\Manager\Log;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BatchSettleHistoryRequest extends FormRequest
{
    use FormRequestTrait;

    public function __construct()
    {
        $this->keys = [
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
            'datas.*'       => 'required|array',
            'datas.*.id'    => 'required|integer',
            'datas.*.acct_name'     => 'nullable', 
            'datas.*.acct_num'      => 'nullable', 
            'datas.*.acct_bank_name' => 'nullable', 
            'datas.*.acct_bank_code' => 'nullable', 
            'datas.*.total_amount'  => 'nullable|integer',
            'datas.*.cxl_amount'    => 'nullable|integer', 
            'datas.*.appr_amount'   => 'nullable|integer', 
            'datas.*.deduct_amount' => 'nullable|integer',
            'datas.*.settle_amount' => 'nullable|integer',
            'dt' => 'required|date',
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

    }

    public function data($target_id, $data)
    {
        return [
            'brand_id' => $this->user()->brand_id,
            $target_id => $data['id'],
            'acct_name' => $data['acct_name'],
            'acct_num' => $data['acct_num'],
            'acct_bank_name' => $data['acct_bank_name'],
            'acct_bank_code' => $data['acct_bank_code'],
            'total_amount' => $data['total_amount'],
            'cxl_amount' => $data['cxl_amount'],
            'appr_amount' => $data['appr_amount'],
            'deduct_amount' => $data['deduct_amount'],
            'settle_amount' => $data['settle_amount'],            
            'trx_amount'=> $data['trx_amount'],
            'settle_dt' => $this->dt,
        ];
    }
}
