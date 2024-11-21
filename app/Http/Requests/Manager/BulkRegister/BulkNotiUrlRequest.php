<?php

namespace App\Http\Requests\Manager\BulkRegister;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BulkNotiUrlRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'send_url',
        'note',
    ];
    public $integer_keys = [
        'mcht_id',
        'pmod_id',
        'noti_status',
    ];
    public function bodyParameters()
    {
        return array_merge($this->getDocsParameters($this->integer_keys), $this->getDocsParameters($this->keys));
    }

    public function authorize()
    {
        return $this->user()->tokenCan(35) ? true : false;
    }

    public function rules()
    {
        $sub = [
            '*' => 'required|array',
            '*.mcht_id' => 'required',
            '*.note' => 'required',
            '*.send_url' => 'required',
            '*.noti_status' => 'required',
        ];
        return $sub;
    }
    
    public function data()
    {
        $datas = [];
        $_datas = $this->all();
        for ($i=0; $i < count($_datas) ; $i++)
        {
            $data = array_merge($this->getParmasBaseKeyV3($_datas[$i], $this->integer_keys, 0), $this->getParmasBaseKeyV3($_datas[$i], $this->keys, ''));
            $datas[] = $data;
        }
        return collect($datas);
    }
}
