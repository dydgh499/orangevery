<?php

namespace App\Http\Requests\Pay;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class HandPayRequest extends FormRequest
{
    use FormRequestTrait;

    public $keys = [
        'pmod_id',
        'yymm',
        'card_num',
        'buyer_name',
        'buyer_phone',
        'installment',
        'amount',
        'ord_num',
        'item_name',
        'auth_num',
        'card_pw',
    ];

    public function authorize(): bool
    {
        return $this->user()->tokenCan(10) ? true : false;
    }

    public function rules(): array
    {
        $sub = [
            'pmod_id' => 'required|integer',
            'yymm' => 'required|integer',
            'card_num' => 'required|string',
            'buyer_name' => 'required|string',
            'buyer_phone' => 'required|string',
            'installment' => 'required|integer',
            'amount' => 'required|string',
            'ord_num'   => 'required|string',
            'item_name' => 'required|string',
            'auth_num'  => 'nullable|string',
            'card_pw'   => 'nullable|string', 
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
        $params['yymm']['description'] = '4자리 YYMM 유효기간.';
        $params['yymm']['example']     = 2311;

        $params['card_num']['description'] = '카드번호.';
        $params['card_num']['example']     = '1234000000005678';

        $params['pmod_id']['example']       = 1023;
        $params['buyer_name']['example']    = '홍길동';
        $params['buyer_phone']['example']   = '01000000000';
        $params['amount']['example']        = 10000;
        $params['item_name']['example']     = '메가커피 아메리카노 L';

        $params['installment']['description'] = '할부기간(0=일시불,2,3,4,5,6,7,8,9,10,11).<br>결제모듈의 할부한도를 초과할 수 없습니다.';
        $params['installment']['example']     = 0;

        $params['ord_num']['description'] = '중복되지 않는 주문번호.<br>50자 이하로 작성해야합니다.';
        $params['ord_num']['example']     = '1700385517624H102302';

        $params['auth_num']['description'] = '인증정보<b>(구인증 필수 값)</b>.<br>카도번호 소유주가 법인인경우 사업자번호, 개인인경우 주민등록번호 앞자리를 입력합니다.';
        $params['auth_num']['example']     = '901212';

        $params['card_pw']['description'] = '카드비밀번호 앞 2자리<b>(구인증 필수 값)</b>.';
        $params['card_pw']['example']     = '34';
        return $params;
    }
    public function data()
    {
        $data = $this->getParmasBaseKey();
        return $data;
    }
}
