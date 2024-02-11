<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class DifferentSettlementInfoRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'rep_mid',
        'sftp_id',
        'sftp_password',
        'pg_type',
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
            'rep_mid' => 'required',
            'sftp_id' => 'required',
            'sftp_password' => 'required',
            'pg_type' => 'required',
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
        return $data;
    }
}
