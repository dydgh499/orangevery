<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Salesforce>
 */
class SalesforceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
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
        $class = rand(0, 5);
        if($class == 0)
            $level = 13;
        else if($class == 1)
            $level = 15;
        else if($class == 2)
            $level = 17;
        else if($class == 3)
            $level = 20;
        else if($class == 4)
            $level = 25;
        else if($class == 5)
            $level = 30;
        $idx = array_rand($addrs);
        $name = $this->faker->name;
        return [
            'brand_id'  => 1,
            'user_name' => $name,
            'nick_name' => $name,
            'user_pw'   => Hash::make('1234'), // password
            'level'     => $level,
            'addr'      => $addrs[$idx],
            'settle_tax_type' => rand(0,3),
            'settle_day' => rand(0,6),
            'settle_cycle' => 0,
            'passbook_img' => $this->faker->imageUrl(256, 256, 'test'),
            'id_img' => $this->faker->imageUrl(256, 256, 'test'),
            'contract_img' => $this->faker->imageUrl(256, 256, 'test'),
            'bsin_lic_img' => $this->faker->imageUrl(256, 256, 'test'),
        ];
    }
}
