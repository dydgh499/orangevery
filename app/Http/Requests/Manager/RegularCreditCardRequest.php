<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class RegularCreditCardRequest extends FormRequest
{
    use FormRequestTrait;

    public function __construct()
    {
        $this->keys = [
            'card_num',
            'note',
        ];
    }

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
        $data['mcht_id'] = $this->mcht_id;
        return $data;
    }
}
