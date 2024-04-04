<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class RegularCreditCardRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'card_num',
        'note',
        'yymm',
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
            'card_num' => 'required',
            'note' => 'required',
            'yymm' => 'nullable',
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
        $params['card_num']['example']  = '1234123412341234';
        $params['note']['example']      = '비고';

        return $params;
    }
    public function data()
    {
        $data = $this->getParmasBaseKey();
        $data['mcht_id'] = isset($this->mcht_id) ? $this->mcht_id : $this->user()->id; //level 10일때
        return $data;
    }
}
