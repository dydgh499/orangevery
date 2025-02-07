<?php

namespace App\Http\Requests\Manager\Merchandise;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class NotiRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'mcht_id',
        'pmod_id',
        'send_url',
        'noti_status',
        'note',
    ];
    public $integer_keys = [
        'send_type'
    ];

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
            'pmod_id' => 'required',
            'send_url' => 'required',
            'noti_status' => 'required',
        ];
        return $this->getRules(array_merge($this->keys, $this->integer_keys), $sub);
    }

    public function attributes()
    {
        return $this->getAttributes(array_merge($this->keys, $this->integer_keys));
    }

    public function bodyParameters()
    {
        return $this->getDocsParameters(array_merge($this->keys, $this->integer_keys));
    }
    public function data()
    {
        return array_merge($this->getParmasBaseKeyV2($this->keys, ''), $this->getParmasBaseKeyV2($this->integer_keys, 0));
    }
}
