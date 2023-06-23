<?php

namespace Database\Factories;


use App\Http\Controllers\Manager\MerchandiseController;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Brand;
use App\Models\Merchandise;
use App\Models\Salesforce;
use Illuminate\Support\Facades\Hash;
use App\Models\Classification;

class MerchandiseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $addrs = [
            '대전광역시 서구 도마동 170-13',
            '대전광역시 유성구 탑립동 839',
            '세종특별자치시 한누리대로 2009',
            '서울특별시 종로구 청와대로 1',
            '서울특별시 노원구 공릉동 609-55',
            '세종특별자치시 한누리대로 2004',
            '세종특별자치시 한누리대로 2003',
            '세종특별자치시 한누리대로 275',
            '세종특별자치시 한누리대로 273',
            '세종특별자치시 한누리대로 583',
        ];
        $idx = array_rand($addrs);
        $brand = Brand::all()->random();
        $cific = Classification::where('brand_id', 1)->where('type', 1)->inRandomOrder()->first();
        return [
            'brand_id'  => $brand->id,
            'user_name' => $this->faker->name,
            'user_pw'   => Hash::make('1234'), // password
            'mcht_name' => $this->faker->name,
            'addr'      => $addrs[$idx],
            'nick_name' => $this->faker->name,
            'profile_img' => $this->faker->imageUrl(256, 256, '가맹점 이미지'),

            'sales5_id' => Salesforce::all()->where('level', 30)->random()->id,
            'sales5_fee' => $this->faker->randomFloat(4, 2, 3)/100,

            'sales4_id' => Salesforce::all()->where('level', 25)->random()->id,
            'sales4_fee' => $this->faker->randomFloat(4, 3, 4)/100,

            'sales3_id' => Salesforce::all()->where('level', 20)->random()->id,
            'sales3_fee' => $this->faker->randomFloat(4, 4, 5)/100,

            'sales2_id' => Salesforce::all()->where('level', 17)->random()->id,
            'sales2_fee' => $this->faker->randomFloat(4, 5, 6)/100,

            'sales1_id' => Salesforce::all()->where('level', 15)->random()->id,
            'sales1_fee' => $this->faker->randomFloat(4, 6, 7)/100,

            'sales0_id' => Salesforce::all()->where('level', 13)->random()->id,
            'sales0_fee' => $this->faker->randomFloat(4, 7, 8)/100,

            'trx_fee' => $this->faker->randomFloat(4, 9, 10)/100,
            'hold_fee' => $this->faker->randomFloat(4, 10, 11)/100,

            'passbook_img' => $this->faker->imageUrl(256, 256, 'test'),
            'id_img' => $this->faker->imageUrl(256, 256, 'test'),
            'contract_img' => $this->faker->imageUrl(256, 256, 'test'),
            'bsin_lic_img' => $this->faker->imageUrl(256, 256, 'test'),
            'profile_img'  => $this->faker->imageUrl(256, 256, 'test'),
            'custom_id'     => $cific->id,
        ];
    }
}
