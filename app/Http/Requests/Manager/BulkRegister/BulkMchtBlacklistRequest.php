<?php

namespace App\Http\Requests\Manager\BulkRegister;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BulkMchtBlacklistRequest extends FormRequest
{
    use FormRequestTrait;
    public $string_keys = [
        'mcht_name',
        'nick_name',
        'phone_num',
        'business_num',
        'resident_num',
        'addr',
        'block_reason',
    ];
    public function bodyParameters()
    {
        return $this->getDocsParameters($this->keys);
    }

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
            '*' => 'required|array',
        ];
        return $sub;
    }
    
    public function data()
    {
        $datas = [];
        $_datas = $this->all();
        for ($i=0; $i < count($_datas) ; $i++)
        {
            $data = array_merge(['brand_id'=>$this->user()->brand_id], $this->getParmasBaseKeyV3($_datas[$i], $this->string_keys, ''));
            $datas[] = $data;
        }
        return collect($datas);
    }
}
