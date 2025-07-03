<?php
    namespace App\Models\Options;

    class initOption
    {
        protected function initIntKey($parent, $key, $default)
        {
            return isset($parent[$key]) ? (int)$parent[$key] : $default;
        }
    }

    class FreeOption extends initOption
    {
        public $use_account_number_duplicate = true; // 계좌번호 중복검사 사용 여부
        public $bonaeja = [
            'user_id'   => '',
            'api_key'   => '',
            'sender_phone' => '',
            'receive_phone'=> '',
            'min_balance_limit' => 0,
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

    class PaidOption extends initOption
    {
        public $yn_delivery_mode = false;
        public function __construct(array $source)
        {
            foreach ($source as $property => $value) 
            {
                if (property_exists($this, $property)) 
                    $this->$property = $value;
            }
        }
    }

    class AuthOption extends initOption
    {
        public function __construct(array $source)
        {
            foreach ($source as $property => $value) 
            {
                if (property_exists($this, $property)) 
                    $this->$property = $value;
            }
        }
    }

    class OvOptions
    {
        public FreeOption $free;
        public PaidOption $paid;
        public AuthOption $auth;

        public function __construct(string $ov_options)
        {
            $json = json_decode($ov_options, true);
            $this->free = new FreeOption(isset($json['free']) ? $json['free'] : []);
            $this->paid = new PaidOption(isset($json['paid']) ? $json['paid'] : []);
            $this->auth = new AuthOption(isset($json['auth']) ? $json['auth'] : []);
        }
    }
?>
