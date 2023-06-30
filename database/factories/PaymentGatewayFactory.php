<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentGateway>
 */
class PaymentGatewayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'brand_id'  => 1,
            'pg_type'   => 1,
            'pg_name'     => '주식회사 페이투스',
            'rep_name'    => '서동균',
            'company_name' => '(주)페이투스',
            'business_num' => '810-81-00347',
            'phone_num' => '02-465-8800',
            'addr' => '서울특별시 금천구 가산디지털1로 168, C동 7층 701B호(가산동, 우림라이온스밸리)',
        ];
    }
}
