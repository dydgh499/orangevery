<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Factory;

use Illuminate\Support\Facades\Hash;
use App\Models\Salesforce;
use Faker\Factory as Faker;

class SalesforceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        for ($i = 1; $i <= 100; $i++) {
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
            $root = Salesforce::create([
                'brand_id'  => 1,
                'user_name' => "salesforce_$i",
                'nick_name' => "영업자 $i",
                'level'     => $level,
                'user_pw'   => Hash::make('1234'), // password
                'passbook_img' => $faker->imageUrl(256, 256, 'test'),
                'id_img' => $faker->imageUrl(256, 256, 'test'),
                'contract_img' => $faker->imageUrl(256, 256, 'test'),
                'bsin_lic_img' => $faker->imageUrl(256, 256, 'test'),
                // 다른 필드들 추가
            ]);
        }
        // Seeder 실행 완료 메시지 출력
        $this->command->info('Salesperson seeder completed.');
    }
}
