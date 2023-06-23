<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Brand::create([
            'dns'   => 'localhost',
            'name'  => 'payvery',
            'theme_css'     => json_encode(['main_color'=>'#2196F3', 'background'=>'#652684']),
            'logo_img'      => 'https://table.comagain.kr/images/test.png',
            'favicon_img'   => 'https://table.comagain.kr/images/logo.png',
            'addr'          => '대전광역시 서구 대덕대로 241번길 20, 549-4호 (둔산동, 청남빌딩)',
            'company_nm'    => '주식회사 퍼플베리',
            'pvcy_rep_nm'   => '김문년',
            'ceo_nm'        => '김문년',
            'business_num'  => '164-87-02282',
            'phone_num'     => 448689419,
            'fax_num'       => '0504-144-8970',
        ]);
    }
}
