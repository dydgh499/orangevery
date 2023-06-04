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
    'not_found'     => '존재하지않은 :attribute가 발견되었어요 😨',
    'not_found_obj'     => '데이터를 찾을 수 없어요 😥',
    'already_exsit'     => ':attribute값이 이미 존재해요 😥',
    'running_out_point' => '포인트가 부족해요 😥',
    'wrong_point_price' => '포인트 금액이 이상하네요.. 😨',
    'wrong_password'    => '패스워드가 정확하지 않아요 😨',

    'after_or_equal'       => ':attribute은 :date 보다 빠르거나 같아야 해요 😥',
    'before_or_equal'      => ':attribute은 :date 보다 늦거나 같아야 해요 😥',

    'accepted' => ':attribute을(를) 동의하지 않으셨어요 😥',
    'active_url' => ':attribute 값이 유효한 URL이 아니에요 😥',
    'after' => ':attribute 값이 :date 보다 이후 날짜가 아니에요 😥',
    'alpha' => ':attribute 값에 문자 외의 값이 포함되어 있어요 😥',
    'alpha_dash' => ':attribute 값에 문자, 숫자, 대쉬(-) 외의 값이 포함되어 있어요 😥',
    'alpha_num' => ':attribute 값에 문자와 숫자 외의 값이 포함되어 있어요 😥',
    'array' => ':attribute 값이 유효한 목록 형식이 아니에요 😥',
    'before' => ':attribute 값이 :date 보다 이전 날짜가 아니에요 😥',
    'between' => [
        'numeric' => ':attribute 값이 :min ~ :max 값 사이에 있어야해요 😥',
        'file' => ':attribute 값이 :min ~ :max 킬로바이트를 벗어나면 안되요 😥',
        'string' => ':attribute 값이 :min ~ :max 글자이어야 해요 😥',
        'array' => ':attribute 값이 :min ~ :max 개를 벗어납니다.',
    ],
    'boolean' => ':attribute 값이 true 또는 false 가 아니에요 😥',
    'confirmed' => ':attribute 와 :attribute 확인 값이 서로 다릅니다.',
    'date' => ':attribute 값이 유효한 날짜가 아니에요 😥',
    'date_format' => ':attribute 값이 :format 형식과 일치해야해요 😥',
    'different' => ':attribute 값이 :other은(는) 서로 다르지 않습니다.',
    'digits' => ':attribute 값이 :digits 자릿수가 아니에요 😥',
    'digits_between' => ':attribute 값이 :min ~ :max 자릿수를 벗어나면 안돼요 😥',
    'distinct' => ':attribute 항목에 서로 중복되는 값이 존재해요 😥',
    'email' => ':attribute 값이 형식에 맞지 않아요 😥',
    'exists' => ':attribute 값에 해당하는 리소스가 존재하지 않아요 😥',
    'file'  => ':attribute 는 파일 이어야해요 😥',
    'filled' => ':attribute 값은 필수 항목이에요 😥',
    'image' => ':attribute 값이 이미지가 아니에요 😥',
    'in' => ':attribute 값이 유효하지 않아요 😥',
    'in_array' => ':attribute 값이 :other 필드의 요소가 아니에요 😥',
    'integer' => ':attribute 값이 정수가 아니에요 😥',
    'ip' => ':attribute 값이 유효한 IP 주소가 아니에요 😥',
    'json' => ':attribute 값이 유효한 JSON 문자열이 아니에요 😥',
    'max' => [
        'numeric' => ':attribute 값이 :max 보다 작아야해요 😨',
        'file' => ':attribute 값이 :max 킬로바이트보다 작아야해요 😨',
        'string' => ':attribute 값이 :max 글자보다 적어야해요 😨',
        'array' => ':attribute 값이 :max 개보다 적어야해요 😨.',
    ],
    'mimes' => ':attribute 값이 :values 와(과) 다른 형식이에요 😨',
    'min' => [
        'numeric' => ':attribute 값이 :min 보다 커야해요 😨',
        'file' => ':attribute 값이 :min 킬로바이트보다 커야해요 😨',
        'string' => ':attribute 값이 :min 글자 이상으로 작성해야해요 😨',
        'array' => ':attribute 값이 :max 개보다 커야해요 😨',
    ],
    'not_in' => ':attribute 값이 유효하지 않아요 😥',
    'numeric' => ':attribute 값이 숫자가 아니에요 😥',
    'present' => ':attribute 필드가 누락되었어요 😥',
    'regex' => ':attribute 값의 형식이 유효하지 않아요 😥',
    'required' => ':attribute 항목은 꼭 입력 하셔야해요 😥',
    'mac_address' => ':attribute 항목의 포멧은 꼭 맥주소 포멧이어야해요 😥',
    'required_if' => ':attribute 값이 누락되었습니다 (:other 값이 :value 일 때는 필수).',
    'required_unless' => ':attribute 값이 누락되었습니다 (:other 값이 :value 이(가) 아닐 때는 필수).',
    'required_with' => ':attribute 값이 누락되었습니다 (:values 값이 있을 때는 필수).',
    'required_with_all' => ':attribute 값이 누락되었습니다 (:values 값이 있을 때는 필수).',
    'required_without' => ':attribute 값이 누락되었습니다 (:values 값이 없을 때는 필수).',
    'required_without_all' => ':attribute 값이 누락되었습니다 (:values 값이 없을 때는 필수).',
    'same' => ':attribute 값이 :other 와 서로 다릅니다.',
    'size' => [
        'numeric' => ':attribute 값이 :size 가 아니에요 😥',
        'file' => ':attribute 값이 :size 킬로바이트가 아니에요 😥',
        'string' => ':attribute 값이 :size 글자가 아니에요 😥',
        'array' => ':attribute 값이 :max 개가 아니에요 😥',
    ],
    'string' => ':attribute 값이 글자가 아니에요 😥',
    'timezone' => ':attribute 값이 올바른 시간대가 아니에요 😥',
    'unique' => ':attribute 값이 이미 존재해요 😥',
    'url' => ':attribute 값이 유효한 URL이 아니에요 😨',

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
        'brand_id'  => '브랜드',
        'user_name' => '유저 ID',
        'user_pw'   => '패스워드',
        'login_type'=> '로그인 타입',
        //advertisement
        'ad_name'   => '광고명',
        'ad_img'    => '광고이미지',
        'ad_type'   => '광고타입',
        //brand
        'dns'       => 'DNS',
        'logo_img'  => '로고 이미지',
        'favicon_img' => 'favicon 아이콘',
        'passbook_img'  => '통장 사본 이미지',
        'contract_img'  => '계약서 이미지',
        'id_img'    => '신분증 사본 이미지',
        'og_img'    => '오픈 그래프 이미지',
        'logo_file'  => '로고 이미지',
        'favicon_file'  => '파비콘',
        'passbook_file' => '통장 사본 이미지',
        'contract_file' => '계약서 이미지',
        'id_file'    => '신분증 사본 이미지',
        'og_file'    => '오픈 그래프 이미지',
        'deposit_day' => '입금일',
        'deposit_amount' => '입금액',                
        'og_description'=> '메타태그 오픈그래프 내용',
        'ceo_nm'    => '대표자명',
        'phone_num' => '연락처',
        'fax_num'   => '팩스번호',
        'template_id'   => '템플릿 ID',
        'theme_css' => 'JSON 포멧의 CSS',
        'company_nm'    => '법인 상호',
        'pvcy_rep_nm'   => '개인정보 책임자명',
        'business_num'  => '사업자 번호',
        'stamp_max_size'    => '스탬프 쿠폰변환 최소개수',
        'stamp_save_count'  => '상품당 스탬프 저장개수',
        'coupon_model_id'   => '쿠폰 발행시 발행될 쿠폰 ID',
        'point_min_amount'  => '포인트 최소 사용 금액',
        'mbr_type'  => '멤버쉽 적용 타입',
        'guide_type'=> '안내 타입',
        //coupon_imgpon model
        'coupon_img'  => '쿠폰 이미지',
        'sale_amt'  => '할인가',
        //coupon publish
        'user_id'   => '유저',
        'coupon_id' => '쿠폰',
        'valid_s_dt'  => '유효기간 시작일',
        'valid_e_dt'  => '유효기간 종료일',
        //device
        'mcht_id'   => '가맹점',
        'partner_id'=> '협럭사',
        'mac_addr'  => '맥주소',
        'comment'   => '비고',
        //index form
        'page'      => '페이지 번호',
        'page_size' => '페이지 사이즈',
        's_dt'      => '시작일',
        'e_dt'      => '종료일',
        //notice
        'title'     => '제목',
        'content'   => '본문',  //product
        //notification
        'noti_type' => '알림 타입',
        'redirect_url'  => '이동 URL',
        'noti_s_dttm'   => '알림시작일',
        'noti_days'     => '알림날짜(일요일~토요일(0~6))',
        //option
        'prod_id'   => '상품',
        'group_id'  => '그룹번호',
        'price' => '가격',
        //order
        'prod_nm'   => '상품명',
        'prod_amt'  => '상품가격',
        'card_nm'   => '카드사명',
        'card_num'  => '카드번호',
        'status'    => '주문상태',
        'instmt'    => '할부',
        'trade_amt' => '거래금액',
        //point
        'purchase_price'=> '결제금액',
        'use_amount'    => '적립포인트',
        'is_cancel'     => '취소 여부',
        'created_at'    => '생성시간',
        //product
        'cate_id'   => '카테고리',
        'product_img'   => '상품 이미지',
        //stamp
        'use_status'    => '사용상태',
        'use_dt'    => '사용일',
        //user
        'level' => '유저등급',
        'nick_name' => '유저명',
        'birth_date'=> '생년월일',
        'mcht_name' => '가맹점 상호',
        'mcht_user_name' => '가맹점 아이디',
        'addr'  => '주소',
        'stamp_flag'=> '스탬프 사용여부',
        'point_flag'=> '포인트 사용여부',
        'point_rate'=> '포인트 변환률',
        'stamp_save_count' => '상품당 스탬프 저장개수',
        'profile_img' => '프로필 이미지',
        'partner_name'  => '협력사 아이디',
        //
        'tags' => '태그',
        'files' => '파일',
        'files.*' => '파일',
        'name' => '이름',   //brand, category, coupon model, option, product
        'email' => '이메일',
        'password' => '비밀번호',
        'password_confirmation' => '비밀번호 확인',
        'code'=> '제품 ID',
        'context'=> '상세설명',
        'tid' => 'TID',
        'cust_nm' => '고객명',
        'appr_dt' => '승인일',
        'appr_num' => '승인번호',
        'phone_num' => '휴대폰번호',
        'hand_cust_nm' => '수기작성성함',
        'hand_phone_num' => '수기작성연락처',
        'issuer_id' => '발급사',
        'type' => '타입',
        'entry_path' => '유입경로',
        'is_deposit' => '입금상태',        
    ],
];
