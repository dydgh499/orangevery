<?php
namespace App\Http\Requests\Manager\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class PayCancelRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'amount',
        'trx_id',
        'pmod_id',
    ];

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $sub = [
            'amount'    => 'required',
            'trx_id'    => 'required',
            'pmod_id'   => 'required',
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
        $params['pmod_id']['description']   = '거래 결제모듈 ID';
        $params['amount']['description']    = '취소할 금액';
        $params['amount']['example']        = '500';
        return $params;
    }
}
