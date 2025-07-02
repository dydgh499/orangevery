<?php

/**
 * The file downloaded from
 * https://github.com/caouecs/Laravel-lang/blob/master/ko/validation.php
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | such as the size rules. Feel free to tweak each of these messages.
    |
    */
    'not_found'     => '존재하지않은 :attribute가 발견되었습니다.',
    'not_found_obj'     => '데이터를 찾을 수 없습니다.',
    'already_exsit'     => '이미 존재하는 :attribute입니다.',
    'running_out_point' => '포인트가 부족합니다.',
    'wrong_password'    => '패스워드가 정확하지 않습니다.',

    'after_or_equal'       => ':attribute 은 :date 보다 빠르거나 같아야합니다.',
    'before_or_equal'      => ':attribute 은 :date 보다 늦거나 같아야합니다.',

    'accepted' => ':attribute을(를) 동의하지 않으셨습니다.',
    'active_url' => ':attribute 값이 유효한 URL이 아닙니다.',
    'after' => ':attribute 값이 :date 보다 이후 날짜가 아닙니다.',
    'alpha' => ':attribute 값에 문자 외의 값이 포함되어 있습니다.',
    'alpha_dash' => ':attribute 값에 문자, 숫자, 대쉬(-) 외의 값이 포함되어 있습니다.',
    'alpha_num' => ':attribute 값에 문자와 숫자 외의 값이 포함되어 있습니다.',
    'array' => ':attribute 값이 유효한 목록 형식이 아닙니다.',
    'before' => ':attribute 값이 :date 보다 이전 날짜가 아닙니다.',
    'between' => [
        'numeric' => ':attribute 값이 :min ~ :max 값 사이에 있어야합니다.',
        'file' => ':attribute 값이 :min ~ :max 킬로바이트를 초과하였습니다.',
        'string' => ':attribute 값이 :min ~ :max 글자이어야 합니다.',
        'array' => ':attribute 값이 :min ~ :max 개를 벗어납니다.',
    ],
    'boolean' => ':attribute 값이 true 또는 false 가 아닙니다.',
    'confirmed' => ':attribute 와 :attribute 확인 값이 서로 다릅니다.',
    'date' => ':attribute 값이 유효한 날짜가 아닙니다.',
    'date_format' => ':attribute 값이 :format 형식과 일치해야합니다.',
    'different' => ':attribute 값이 :other은(는) 서로 다르지 않습니다.',
    'digits' => ':attribute 값이 :digits 자릿수가 아닙니다.',
    'digits_between' => ':attribute 값이 :min ~ :max 자릿수를 벗어납니다.',
    'distinct' => ':attribute 항목에 서로 중복되는 값이 존재합니다.',
    'email' => ':attribute 값이 형식에 맞지 않습니다.',
    'exists' => ':attribute 값에 해당하는 리소스가 존재하지 않습니다.',
    'file'  => ':attribute 는 파일 이어야합니다.',
    'filled' => ':attribute 값은 필수 항목입니다.',
    'image' => ':attribute 값이 이미지가 아닙니다.',
    'in' => ':attribute 값이 유효하지 않습니다.',
    'in_array' => ':attribute 값이 :other 필드의 요소가 아닙니다.',
    'integer' => ':attribute 값이 정수가 아닙니다.',
    'ip' => ':attribute 값이 유효한 IP 주소가 아닙니다.',
    'json' => ':attribute 값이 유효한 JSON 문자열이 아닙니다.',
    'max' => [
        'numeric' => ':attribute 값이 :max 보다 작아야합니다.',
        'file' => ':attribute 값이 :max 킬로바이트보다 큽니다.',
        'string' => ':attribute 값이 :max 글자보다 많습니다.',
        'array' => ':attribute 값이 :max 개보다 많습니다.',
    ],
    'mimes' => ':attribute 값이 :values 와(과) 다른 형식입니다.',
    'min' => [
        'numeric' => ':attribute 값이 :min 보다 작습니다.',
        'file' => ':attribute 값이 :min 킬로바이트보다 작습니다.',
        'string' => ':attribute 값이 :min 글자 이상으로 작성하셔야합니다.',
        'array' => ':attribute 값이 :max 개보다 적습니다.',
    ],
    'not_in' => ':attribute 값이 유효하지 않습니다.',
    'numeric' => ':attribute 값이 숫자가 아닙니다.',
    'present' => ':attribute 필드가 누락되었습니다.',
    'regex' => ':attribute 값의 형식이 유효하지 않습니다.',
    'required' => ':attribute 항목은 꼭 입력 하셔야합니다.',
    'mac_address' => ':attribute 항목의 포멧은 꼭 맥주소 포멧이어야합니다.',
    'required_if' => ':attribute 값이 누락되었습니다 (:other 값이 :value 일 때는 필수).',
    'required_unless' => ':attribute 값이 누락되었습니다 (:other 값이 :value 이(가) 아닐 때는 필수).',
    'required_with' => ':attribute 값이 누락되었습니다 (:values 값이 있을 때는 필수).',
    'required_with_all' => ':attribute 값이 누락되었습니다 (:values 값이 있을 때는 필수).',
    'required_without' => ':attribute 값이 누락되었습니다 (:values 값이 없을 때는 필수).',
    'required_without_all' => ':attribute 값이 누락되었습니다 (:values 값이 없을 때는 필수).',
    'same' => ':attribute 값이 :other 와 서로 다릅니다.',
    'size' => [
        'numeric' => ':attribute 값이 :size 가 아닙니다.',
        'file' => ':attribute 값이 :size 킬로바이트가 아닙니다.',
        'string' => ':attribute 값이 :size 글자가 아닙니다.',
        'array' => ':attribute 값이 :max 개가 아닙니다.',
    ],
    'string' => ':attribute 값이 글자가 아닙니다.',
    'uploaded' => ':attribute 업로드에 실패했습니다. 파일크기를 줄여주세요.',
    'timezone' => ':attribute 값이 올바른 시간대가 아닙니다.',
    'unique' => ':attribute 값이 이미 존재합니다.',
    'url' => ':attribute 값이 유효한 URL이 아닙니다.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom'    => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */
    'attributes' => [
        //login
        'last_login_at' => '로그인 시간',
        'last_login_ip' => '로그인 IP',
        'brand_id'  => '브랜드',
        'user_name' => '유저 ID',
        'user_pw'   => '패스워드',
        'user_pw_check' => '패스워드 재확인',
        'login_type'=> '로그인 타입',
        'profile_img' => '프로필 이미지',
        //brand
        'dns'       => 'DNS',
        'logo_img'  => '로고 이미지',
        'favicon_img' => 'favicon 아이콘',
        'passbook_img'  => '통장 사본 이미지',
        'contract_img'  => '계약서 이미지',
        'bsin_lic_img'  => '사업자 등록증 이미지',
        'id_img'    => '신분증 사본 이미지',
        'og_img'    => '오픈 그래프 이미지',
        'login_img' => '로그인 화면 이미지',
        'logo_file'  => '로고 이미지',
        'favicon_file'  => '파비콘',
        'passbook_file' => '통장 사본 이미지',
        'contract_file' => '계약서 이미지',
        'bsin_lic_file' => '사업자등록증 이미지',
        'profile_file' => '프로필 이미지',
        'id_file'    => '신분증 사본 이미지',
        'og_file'    => '오픈 그래프 이미지',
        'og_description'=> '메타태그 오픈그래프 내용',
        'ceo_name'    => '대표자명',
        'phone_num' => '연락처',
        'fax_num'   => '팩스번호',
        'theme_css' => '테마색상',
        'company_name'    => '법인 상호',
        'business_num'  => '사업자등록번호',
        //index form
        'page'      => '페이지 번호',
        'page_size' => '페이지 사이즈',
        's_dt'      => '시작일',
        'e_dt'      => '종료일',
        'is_cancel'     => '취소 여부',
        'created_at'    => '생성시간',
        //user
        'level' => '유저등급',
        'nick_name' => '대표자명',
        'addr'  => '주소',
        //
        'tags' => '태그',
        'files' => '파일',
        'files.*' => '파일',
        'name' => '이름',
        'email' => '이메일',
        'password' => '비밀번호',
        'password_confirmation' => '비밀번호 확인',

        //payment_modules
        'pg_id' => 'PG사',
        'ps_id' => '구간',
        'module_type' => '결제모듈 타입',
        'is_old_auth' => '비인증/구인증 여부',
        'installment' => '할부',
        'note' => '비고',
        'resident_num' => '주민등록번호',
        'acct_bank_name' => '은행명',
        'acct_bank_code' => '은행코드',
        'acct_name' => '예금주',
        'acct_num'  => '계좌번호',
        'trx_fee'   => '거래 수수료',
        'tid' => 'TID',
        'issuer' => '발급사',
        'type' => '타입',
        'api_key' => 'API KEY',
        'sub_key' => 'SUB KEY(License)',
        'mid' => 'MID',

        'fin_id' => '금융벤 ID',
        //finance van
        'receive_phone' => '수신자 전화번호',
        'sender_phone' => '발신자 전화번호',
        'corp_name' => '법인명',
        'corp_code' => '법인코드',
        'bank_code' => '은행코드',
        'withdraw_acct_num' => '출금계좌',
        'enc_key' => 'enc key',
        'iv' => 'iv',
        'finance_company_num' => '금융 VAN 타입',
        // payment_gateways
        'pg_type' => 'PG 타입',
        'pg_name' => 'PG사 별칭',
        'rep_name'=> '대표자명',
        'id' => '고유번호',
        // pay
        'pmod_id'   => '결제모듈 고유번호',
        'amount'    => '구매금액',
        'ord_num'   => '주문번호',
        'return_url' => 'Return URL',
        'card_num'  => '카드번호',
        'yymm'      => '유효기간',
        'auth_num'   => '인증정보',     //opt
        'card_pw'   => '카드 비밀번호', //opt
        'temp'      => '임시 값',
        'item_name' => '상품명',
        'buyer_name' => '구매자명',
        'buyer_phone' => '휴대폰 번호',
        // cancel
        'trx_id'    => '거래번호',
        // util
        'msg' => '메세지',        
        'deposit_history' => '입금내역',
        'oper_id' => '운영자',
        'work_s_at' => '작업시작 시간',
        'work_e_at' => '작업종료 시간',
        // transactions
        'cxl_seq' => '취소 회차',
        'ps_fee' => '구간 수수료',
        'acquirer' => '매입사',
        'ori_trx_id' => '원거래번호',
        'trx_at' => '거래시간',
        'issuer_code' => '발급사 코드',
        'acquirer_code' => '매입사 코드',
        // fee change history
        'change_status' => '변경상태',
        'is_delete' => '삭제여부',
        'updated_at' => '마지막 업데이트 시간',
        'apply_dt' => '적용일',
    ],
];
