<?php
    namespace App\Http\Controllers\External;

    use App\Models\Operator;
    use App\Models\Pay\PaymentModule;
    use App\Models\Service\OperatorIP;
    use App\Models\Service\PaymentSection;

    use App\Http\Traits\ManagerTrait;
    use App\Http\Traits\ExtendResponseTrait;

    use App\Http\Controllers\Auth\AuthPasswordChange;
    use App\Http\Controllers\Auth\AuthOperatorIP;

    use App\Http\Requests\External\SignUpRequest;
    use App\Http\Requests\External\SignCheckRequest;
    use App\Http\Controllers\Ablilty\BrandInfo;

    use Illuminate\Support\Facades\Hash;

    class DeliveryAgencyController
    {
        use ManagerTrait, ExtendResponseTrait;

        private function createOperator($request, $brand)
        {
            $current = date("Y-m-d H:i:s");
            return Operator::create([
                'brand_id'  => $brand['id'],
                'user_name' => $request->user_name,
                'user_pw'   => Hash::make($request->user_pw.$current),
                'nick_name' => $request->nick_name,
                'phone_num' => $request->phone_num,
                'level'     => 35,
                'profile_img' => '/utils/avatars/'.rand(1, 25).'.svg',
            ]);
        }

        private function createOperatorIP($request, $brand, $oper)
        {
            return OperatorIP::create([
                'brand_id'  => $brand['id'],
                'oper_id'   => $oper->id,
                'enable_ip'   => $request->request_ip,
            ]);
        }

        private function createPsFee($request, $brand, $oper)
        {
            return PaymentSection::create([
                'brand_id'  => $brand['id'],
                'oper_id'   => $oper->id,
                'name'      => '수수료율',
                'trx_fee'   => $request->trx_fee,
            ]);
        }

        private function createPaymentModule($request, $brand, $oper, $ps)
        {
            return PaymentModule::create([
                'brand_id'  => $brand['id'],
                'oper_id'   => $oper->id,
                'pg_id'     => env("DELIVERY_PG_ID"),
                'ps_id'     => $ps->id,
                'module_type' => 4,
                'api_key'   => $request->pay_key,
                'mid'       => $request->mid,
                'tid'       => $request->tid,
                'note'      => '빌키결제'
            ]);
        }

        public function signUp(SignUpRequest $request)
        {
            // 접속 도메인 검증
            if(BrandInfo::isDeliveryBrand())
            {
                $brand = BrandInfo::getBrandByDNS($_SERVER['HTTP_HOST']);
                [$result, $msg] = AuthPasswordChange::registerValidate($request->user_name, $request->user_pw);
                if($result === false)
                    return $this->extendResponse(954, $msg, []);
                else
                {
                    // 계정 추가(직원)
                    $operator = $this->createOperator($request, $brand);
                    if($operator)
                    {
                        // IP 추가
                        $this->createOperatorIP($request, $brand, $operator);
                        // 수수료율 연동      
                        $pay_section = $this->createPsFee($request, $brand, $operator);
                        $this->createPaymentModule($request, $brand, $operator, $pay_section);
                        AuthOperatorIP::init($brand['id']);
                        return $this->response(1);
                    }
                    else
                        return $this->extendResponse(9999, '계정 생성 실패');  
                }
            }
            else
                return $this->extendResponse(9999, '대상 전산이 아닙니다.');  
        }

        public function signCheck(SignCheckRequest $request)
        {
            if(BrandInfo::isDeliveryBrand())
            {
                $brand = BrandInfo::getBrandByDNS($_SERVER['HTTP_HOST']);
                $is_signed = Operator::where('brand_id', $brand['id'])
                    ->where('user_name', $request->user_name)
                    ->exists();
                if($is_signed)
                    return $this->response(0); 
                else
                    return $this->extendResponse(9999, '가입되지 않은 회원입니다.');  
            }
            else
                return $this->extendResponse(9999, '대상 전산이 아닙니다.');  
        }
}
