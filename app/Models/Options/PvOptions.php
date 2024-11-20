<?php
    namespace App\Models\Options;

    class FreeOption
    {
        public $use_search_date_detail = true;
        public $use_tid_duplicate = true;
        public $use_mid_duplicate = false;
        public $use_fix_table_view = true;
        public $fix_table_size = 749;
        public $excel_search_filter = true;
        public $resident_num_masking = true;
        public $pay_module_detail_view = false;

        public $sales_slip = [
            'merchandise' => [
                'company_name' => '',
                'rep_name' => '',
                'phone_num' => '',
                'business_num' => '',
                'addr' => '',
            ]
        ];
        public $bonaeja = [
            'user_id'   => '',
            'api_key'   => '',
            'sender_phone' => '',
            'receive_phone'=> '',
            'min_balance_limit' => 0,
        ];
        
        public $default = [
            'installment' => 0,
            'abnormal_trans_limit' => 0,
            'is_show_fee' => 0,
        ];
        
        public $secure = [
            'mcht_id_level' => 1,
            'mcht_pw_level' => 2,
            'sales_id_level' => 1,
            'sales_pw_level' => 2,
            'account_lock_limit' => 5,
            'login_only_operate' => 0,
        ];

        private function initIntKey($parent, $key, $default)
        {
            return isset($parent[$key]) ? (int)$parent[$key] : $default;
        }

        public function __construct(array $source)
        {
            foreach ($source as $property => $value) 
            {
                if (property_exists($this, $property)) 
                    $this->$property = $value;
            }
            
            $this->default['installment'] = $this->initIntKey($this->default, 'installment', 0);
            $this->default['is_show_fee'] = $this->initIntKey($this->default, 'is_show_fee', 0);
            $this->default['abnormal_trans_limit'] = $this->initIntKey($this->default, 'abnormal_trans_limit', 0);
            
            $this->secure['mcht_id_level'] = $this->initIntKey($this->secure, 'mcht_id_level', 1);
            $this->secure['mcht_pw_level'] = $this->initIntKey($this->secure, 'mcht_pw_level', 2);
            $this->secure['sales_id_level'] = $this->initIntKey($this->secure, 'sales_id_level', 1);
            $this->secure['sales_pw_level'] = $this->initIntKey($this->secure, 'sales_pw_level', 2);
            $this->secure['account_lock_limit'] = $this->initIntKey($this->secure, 'account_lock_limit', 5);
            $this->secure['login_only_operate'] = $this->initIntKey($this->secure, 'login_only_operate', 0);
        }
    }

    class PaidOption 
    {
        public $use_dup_pay_validation = true; // 중복결제 검증 사용 여부
        public $subsidiary_use_control = false; // 하위 영업점 전산 사용 ON/OFF
        public $use_acct_verification = false;  // 예금주 검증
        public $use_realtime_deposit = false;   // 실시간 결제모듈
        public $use_issuer_filter = false;      // 카드사 필터링
        public $use_forb_pay_time = false;      // 결제금지시간 지정 사용 여부
        public $use_hand_pay_sms = false;       // 수기결제 SMS
        public $use_pay_verification_mobile = false; // 결제전 휴대폰 인증
        public $use_pay_limit = false;          // 결제한도 지정 사용 여부
        public $use_online_pay = false;         // 온라인 결제 사용 여부
        public $use_tid_create = false;         // tid 생성버튼 사용여부
        public $use_mid_create = false;         // mid 생성버튼 사용여부
        public $use_pmid = false;               // PMID 사용여부
        public $use_regular_card = false;       // 단골고객 카드등록
        public $use_collect_withdraw = false;   // 모아서 출금
        public $use_collect_withdraw_scheduler = false; // 모아서 출금 스케줄링
        public $use_withdraw_fee = true;       // 출금 수수료
        public $use_head_office_withdraw = false; // 가상계좌 출금
        public $use_noti = false;               // 노티 사용여부
        public $use_finance_van_deposit = false; // 금융 VAN 송금 사용여부
        public $use_before_brand_info = false;  // 이전 서비스 정보 사용
        public $use_multiple_hand_pay = false;  // 다중 수기결제
        public $use_mcht_blacklist = false;     // 가맹점 블랙리스트
        public $use_part_cancel = false;        // 부분취소
        public $use_settle_hold = false;        // 지급보류
        public $use_hide_account = false;       // 계좌숨김
        public $sales_parent_structure = false;
        public $use_specified_limit = false;    // 지정시간 제한
        public $use_syslink = false;    //syslink 선정산 사용여부
        public $use_product = false;
        public $use_cancel_all_allow = false;
        public $use_bill_key = false; // 빌키사용 여부

        public function __construct(array $source)
        {
            foreach ($source as $property => $value) 
            {
                if (property_exists($this, $property)) 
                    $this->$property = $value;
            }
        }
    }

    class AuthOption
    {
        public $levels = [
            'dev_use'       =>  false,
            'dev_name'      => '개발사',
            'sales5_use'    => true,
            'sales5_name'   => '지사',
            'sales4_use'    => false,
            'sales4_name'   => '하위지사',
            'sales3_use'    => true,
            'sales3_name'   => '총판',
            'sales2_use'    => false,
            'sales2_name'   => '하위총판',
            'sales1_use'    => true,
            'sales1_name'   => '대리점',
            'sales0_use'    => false,
            'sales0_name'   => '하위대리점'
        ];
        public $visibles = [
            'abnormal_trans_sales' => true,
        ];
        
        public function __construct(array $source)
        {
            foreach ($source as $property => $value) 
            {
                if (property_exists($this, $property)) 
                    $this->$property = $value;
            }
        }
    }

    class PvOptions
    {
        public FreeOption $free;
        public PaidOption $paid;
        public AuthOption $auth;

        public function __construct(string $pv_options)
        {
            $json = json_decode($pv_options, true);
            $this->free = new FreeOption(isset($json['free']) ? $json['free'] : []);
            $this->paid = new PaidOption(isset($json['paid']) ? $json['paid'] : []);
            $this->auth = new AuthOption(isset($json['auth']) ? $json['auth'] : []);
        }
    }
?>
