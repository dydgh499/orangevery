<?php
    namespace App\Models\Options;

    class FreeOption 
    {
        public $use_hand_pay = false;
        public $use_auth_pay = false;
        public $use_simple_pay = false;
        public $sales_slip = [
            'merchandise' => [
                'rep_nm' => '',
                'phone_num' => '',
                'resident_num' => '',
                'business_num' => '',
                'addr' => '',
            ]
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

    class PaidOption 
    {
        public $use_dup_pay_validation = false; // 중복결제 검증 사용 여부
        public $subsidiary_use_control = false; // 하위 영업점 전산 사용 ON/OFF
        public $use_acct_verification = false;  // 예금주 검증
        public $use_realtime_deposit = false;   // 실시간 결제모듈
        public $use_hand_pay_drct = false;      // 수기결제 직접입력(가맹점)
        public $use_issuer_filter = false;      // 카드사 필터링
        public $use_forb_pay_time = false;      // 결제금지시간 지정 사용 여부
        public $use_hand_pay_sms = false;       // 수기결제 SMS
        public $use_pay_limit = false;          // 결제한도 지정 사용 여부
        
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

    class ThemeCSS 
    {
        public $main_color = '#6E34C5';
        public function __construct(string $theme_css)
        {
            $json = json_decode($theme_css, true);  
            $this->main_color = isset($json['main_color']) ? $json['main_color'] : '#6E34C5';
        }
    }
?>
