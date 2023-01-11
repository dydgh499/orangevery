<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'business_nm'   => fake()->name(),
            'email'     => 'admin@demo.com',
            'email_verified_at' => now(),
            'password'  => Hash::make('admin'), // password
            'level'     => 50,
            'avatar'    => '/resources/images/avatars/avatar-'.fake()->numberBetween(1, 6).'.png',
            'phone'     => fake()->unique->e164PhoneNumber,

        ]);
        User::create([
            'business_nm'   => fake()->name(),
            'email'     => 'client@gmail.com',
            'email_verified_at' => now(),
            'password'  => Hash::make('client'), // password
            'level'     => 10,
            'avatar'    => '/resources/images/avatars/avatar-'.fake()->numberBetween(1, 6).'.png',
            'phone'     => fake()->unique->e164PhoneNumber,
        ]);
        User::create([
            'business_nm'   => fake()->name(),
            'email'     => 'agcy@gmail.com',
            'email_verified_at' => now(),
            'password'  => Hash::make('1234'), // password
            'level'     => 15,
            'avatar'    => '/resources/images/avatars/avatar-'.fake()->numberBetween(1, 6).'.png',
            'phone'     => fake()->unique->e164PhoneNumber,
        ]);
        User::create([
            'business_nm'   => fake()->name(),
            'email'     => 'sf@gmail.com',
            'email_verified_at' => now(),
            'password'  => Hash::make('1234'), // password
            'level'     => 20,
            'avatar'    => '/resources/images/avatars/avatar-'.fake()->numberBetween(1, 6).'.png',
            'phone'     => fake()->unique->e164PhoneNumber,
        ]);
        User::factory()->count(25)->create();
    }
}
