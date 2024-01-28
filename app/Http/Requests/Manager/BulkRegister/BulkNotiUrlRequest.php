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
        'noti_status',
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
            $data = [];
            for ($j=0; $j < count($this->keys) ; $j++) 
            {
                $key = $this->keys[$j];
                $data[$key] = isset($_datas[$i][$key]) ? $_datas[$i][$key] : '';
            }
            for ($j=0; $j < count($this->integer_keys) ; $j++) 
            {
                $key = $this->integer_keys[$j];
                $data[$key] = isset($_datas[$i][$key]) ? $_datas[$i][$key] : 0;
            }
            $datas[] = $data;
        }
        return collect($datas);
    }
}
