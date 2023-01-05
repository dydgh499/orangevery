<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        Brand::factory()->count(1)->create();
        Brand::create([
            'dns'   => 'www.redvery.kr',
            'name'  => '레드베리',
            'logo_img'      => 'https://www.redvery.kr/images/test.png',
            'favicon_img'   => 'https://www.redvery.kr/images/logo.png',
            'company_addr'  => '대전광역시 서구 대덕대로 241번길 20, 549-4호 (둔산동, 청남빌딩)',
            'company_nm'    => '주식회사 퍼플베리',
            'pvcy_rep_nm'   => '김문년',
            'ceo_nm'        => '김문년',
            'business_num'  => '164-87-02282',
            'phone_num'     => '044-868-9419',
            'fax_num'       => '0504-144-8970',
        ]);
        Brand::create([
            'dns'   => 'pg.ez-pg.co.kr',
            'name'  => 'EZ Payment Gateway',
            'logo_img'      => 'https://pg.ez-pg.co.kr/images/ezpg.png',
            'favicon_img'   => 'https://pg.ez-pg.co.kr/images/ezpg.png',
            'company_addr'  => '서울특별시 강남구 도산대로 157, 15층(신사동, 신웅타워2)',
            'company_nm'    => '주식회사 이지피쥐',
            'pvcy_rep_nm'   => '김도형, 홍승훈',
            'ceo_nm'        => '김도형, 홍승훈',
            'business_num'  => '635-81-00256',
            'phone_num'     => '1522-3434',
            'fax_num'       => '02-541-4144',
        ]);
    }
}
