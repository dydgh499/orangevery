<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Service\Classification;
use App\Models\Service\PaymentGateway;
use App\Models\Service\PaymentSection;
use App\Models\Merchandise;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Merchandise\PaymentModule>
 */
class PaymentModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $pg = PaymentSection::all()->random();
        $md_type = rand(0, 3);
        if($md_type == 0)
        {
            $cific = Classification::where('brand_id', 1)->where('type', 0)->inRandomOrder()->first();
            $tmn_id = $cific->id;
        }
        else
            $tmn_id = 0;

        return [
            'brand_id'  => 1,
            'mcht_id'   => Merchandise::all()->random()->id,
            'pg_id'     => $pg->pg_id,
            'ps_id'     => $pg->id,
            'settle_type' => rand(0, 7),
            'settle_fee' => rand(0, 100),
            'tid' => $this->faker->unique->isbn10(),
            'mid' => $this->faker->randomNumber(),
            'api_key' => $this->faker->unique->isbn13(),
            'module_type' => $md_type,
            'installment' => rand(2, 11),
            'terminal_id' => $tmn_id,
            'note' => $this->faker->name,
        ];
    }
}
