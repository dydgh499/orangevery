<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Operator;
use Illuminate\Support\Facades\Hash;

class OperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Operator::create([
            'brand_id'  => 1,
            'user_name' => 'master',
            'user_pw'   => Hash::make('1234'),
            'level'     => 50,
        ]);
        Operator::create([
            'brand_id'  => 1,
            'user_name' => 'master',
            'user_pw'   => Hash::make('1234'),
            'level'     => 50,
        ]);
        Operator::create([
            'brand_id'  => 1,
            'user_name' => 'admin',
            'user_pw'   => Hash::make('1234'),
            'level'     => 40,
        ]);
    }
}
