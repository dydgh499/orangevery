<?php

namespace App\Http\Requests\Manager\Settle;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class CollectWithdrawRequestV2 extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'samw_code',
        'withdraw_amount',
        'acct_num',
        'acct_name',
        'acct_bank_code',
        'acct_bank_name',
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
            'samw_code' => 'required|string',
            'withdraw_amount' => 'required|integer',
            'acct_num'  => 'required|string',
            'acct_name' => 'required|string',
            'acct_bank_code' => 'required|string',
            'acct_bank_name' => 'required|string',
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
        $params['samw_code']['description'] = 'SAMW CODE';
        $params['samw_code']['example']     = '2BWHVKQS7P';
        $params['withdraw_amount']['description'] = '출금요청할 금액.<br>출금가능금액을 초과할 수 없습니다.';
        $params['withdraw_amount']['example']     = 1000;
        $params['acct_num']['description']      = '입금 계좌번호.<br>(AES-256-CBC 암호화 필요)';
        $params['acct_name']['description']     = '예금주명.<br>(AES-256-CBC 암호화 필요)';
        $params['acct_bank_name']['description'] = '입금 은행명.<br>(AES-256-CBC 암호화 필요)';
        $params['acct_bank_code']['description'] = '입금 은행코드.<br>(AES-256-CBC 암호화 필요)';
        $params['acct_num']['example']     = '141020101231321';
        $params['acct_name']['example']     = '홍길동';
        $params['acct_bank_name']['example']     = "003";
        $params['acct_bank_code']['example']     = "기업은행";
        
        // $enc_key = "23C298FA47F111EFA4C8ECB1D784C284";
        // $iv      = "A4C8XCB1DF84C284";
        return $params;
    }
    public function data()
    {
        return $this->getParmasBaseKey();
    }
}
