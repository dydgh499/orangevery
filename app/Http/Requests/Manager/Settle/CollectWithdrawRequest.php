<?php

namespace App\Http\Requests\Manager\Settle;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

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
            'withdraw_amount' => 'required|numeric',
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
        $params['withdraw_amount']['description'] = '출금요청할 금액.<br>출금가능금액을 초과할 수 없습니다.';
        $params['withdraw_amount']['example']     = 1000;
        return $params;
    }
    public function data()
    {
        $data = $this->getParmasBaseKey();
        return $data;
    }
}
