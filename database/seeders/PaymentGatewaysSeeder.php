<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;

class PaymentGatewaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $res = PaymentGateway::create([
            'brand_id'  => 1,
            'pg_type'   => 1,
            'pg_nm'     => '주식회사 페이투스',
            'rep_nm'    => '서동균',
            'company_nm' => '(주)페이투스',
            'business_num' => '810-81-00347',
            'phone_num' => '02-465-8800',
            'addr' => '서울특별시 금천구 가산디지털1로 168, C동 7층 701B호(가산동, 우림라이온스밸리)',
        ]);
        $res = PaymentGateway::create([
            'brand_id'  => 1,
            'pg_type'   => 5,
            'pg_nm'     => '헥토파이넨셜',
            'rep_nm'    => '최종원',
            'company_nm' => '헥토파이넨셜',
            'business_num' => '101-81-63383',
            'phone_num' => '1600-5220',
            'addr' => '서울특별시 금천구 가산디지털1로 168, C동 7층 701B호(가산동, 우림라이온스밸리)',
        ]);
        $res = PaymentGateway::create([
            'brand_id'  => 1,
            'pg_type'   => 10,
            'pg_nm'     => '코리아결제시스템',
            'rep_nm'    => '박형석',
            'company_nm' => '(주)코리아결제시스템',
            'business_num' => '810-81-00347',
            'phone_num' => '02-465-8400',
            'addr' => '서울특별시 금천구 가산디지털1로 168, C동 7층 701B호(가산동, 우림라이온스밸리)',
        ]);
        $res = PaymentGateway::create([
            'brand_id'  => 1,
            'pg_type'   => 12,
            'pg_nm'     => '이지피쥐',
            'rep_nm'    => '김도형',
            'company_nm' => '주식회사 이지피쥐',
            'business_num' => '635-81-00256',
            'phone_num' => '02-1522-3434',
            'addr' => '서울특별시 금천구 가산디지털1로 168, C동 7층 701B호(가산동, 우림라이온스밸리)',
        ]);
    }
}
