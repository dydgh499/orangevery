<?php

namespace App\Http\Requests\Manager\Merchandise;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;
use App\Http\Controllers\Ablilty\Ablilty;

class SpecifiedTimeDisablePaymentRequest extends FormRequest
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
        if(Ablilty::isOperator($this))
            return true;
        else if(Ablilty::isSalesforce($this))
            return Ablilty::salesAuthValidate($this, $this->id);
        else
            return false;
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
