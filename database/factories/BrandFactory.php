<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'dns'   => 'localhost',
            'name'  => 'redvery',
            'logo_img'      => 'http://localhost/images/logo.png',
            'favicon_img'   => 'http://localhost/images/logo.png',
            'company_addr'  => '대전광역시 서구 대덕대로 241번길 20, 549-4호 (둔산동, 청남빌딩)',
            'company_nm'    => '주식회사 퍼플베리',
            'pvcy_rep_nm'   => '김문년',
            'ceo_nm'        => '김문년',
            'business_num'  => '164-87-02282',
            'phone_num'     => '044-868-9419',
            'fax_num'       => '0504-144-8970',
        ];
    }
}
