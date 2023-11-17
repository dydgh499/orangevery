<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;
use Carbon\Carbon;

class CollectWithdrawRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'withdraw_amount',
    ];

    public function authorize(): bool
    {
        return $this->user()->tokenCan(10) ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $sub = [
            'withdraw_amount' => 'required',
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
        $data = $this->getParmasBaseKey();
        $data['brand_id'] = $this->user()->brand_id;
        $data['mcht_id'] = $this->user()->id;        
        $data['withdraw_date'] = Carbon::now()->format('Y-m-d');
        $data['acct_num'] = $this->user()->acct_num;
        $data['acct_name'] = $this->user()->acct_name;
        $data['acct_bank_name'] = $this->user()->acct_bank_name;
        $data['acct_bank_code'] = $this->user()->acct_bank_code;
        return $data;
    }
}
