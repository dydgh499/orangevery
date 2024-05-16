<?php

namespace App\Http\Requests\Manager\Merchandise;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;


class SpecifiedTimeDisableLimitPaymentRequest extends FormRequest
{
    use FormRequestTrait;

    public $keys = [];
    public $nullable_keys = [
        'disable_s_tm',
        'disable_e_tm',
    ];
    public $integer_keys = [
        'mcht_id',
        'disable_type',
    ];
    public function authorize()
    {
        return $this->user()->tokenCan(35) ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $sub = [
            'disable_s_tm'   => 'string|required',
            'disable_e_tm' => 'string|required',
            'mcht_id' => 'required',
            'disable_type' => 'required',
        ];
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return $this->getAttributes(array_merge($this->nullable_keys, $this->integer_keys));
    }

    public function data()
    {
        return array_merge($this->getParmasBaseKeyV2($this->nullable_keys, null), $this->getParmasBaseKeyV2($this->integer_keys, 0));
    }
}
