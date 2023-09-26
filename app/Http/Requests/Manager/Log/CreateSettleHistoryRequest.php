<?php

namespace App\Http\Requests\Manager\Log;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class CreateSettleHistoryRequest extends FormRequest
{
    use FormRequestTrait;

    public function __construct()
    {
        $this->keys = [
            'id',
            'acct_name', 
            'acct_num', 
            'acct_bank_name', 
            'acct_bank_code', 
            'total_amount',
            'cxl_amount', 
            'appr_amount', 
            'deduct_amount',
            'settle_amount',
            'dt',
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
            'id'        => 'required|integer',
            'acct_name'   => 'required', 
            'acct_num'  => 'required', 
            'acct_bank_name' => 'required', 
            'acct_bank_code' => 'required', 
            'total_amount' => 'required|integer',
            'cxl_amount' => 'required|integer', 
            'appr_amount' => 'required|integer', 
            'deduct_amount' => 'required|integer',
            'settle_amount' => 'required|integer',
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

    public function data($target_id)
    {
        return [
            'brand_id' => $this->user()->brand_id,
            $target_id => $this->id,
            'acct_name' => $this->acct_name,
            'acct_num' => $this->acct_num,
            'acct_bank_name' => $this->acct_bank_name,
            'acct_bank_code' => $this->acct_bank_code,
            'total_amount' => $this->total_amount,
            'cxl_amount' => $this->cxl_amount,
            'appr_amount' => $this->appr_amount,
            'deduct_amount' => $this->deduct_amount,
            'settle_amount' => $this->settle_amount,
            'trx_amount'=> $this->trx_amount,
            'settle_dt' => $this->dt,
        ];
    }
}
