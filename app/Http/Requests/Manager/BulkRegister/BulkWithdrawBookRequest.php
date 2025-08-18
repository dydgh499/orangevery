<?php

namespace App\Http\Requests\Manager\BulkRegister;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class BulkWithdrawBookRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'acct_num',             // 입금 계좌번호
        'withdraw_book_time',   // 이체 예정 시각
        'company_name',
    ];
    public $integer_keys = [
        'fin_id',
        'withdraw_amount', // 출금 금액
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
            '*.fin_id' => 'required',
            '*.acct_num' => 'required', // 입금 계좌번호
            '*.withdraw_amount' => 'required', // 출금 금액
            '*.withdraw_book_time' => 'required', // 이체 예정 시각
            
        ];
        return $sub;
    }
    
    public function data()
    {
        $datas = [];
        $_datas = $this->all();
        for ($i=0; $i < count($_datas) ; $i++)
        {
            $data = array_merge(
                $this->getParmasBaseKeyV3($_datas[$i], $this->integer_keys, 0), 
                $this->getParmasBaseKeyV3($_datas[$i], $this->keys, '')
            );
            $datas[] = $data;
        }
        return collect($datas);
    }
}
