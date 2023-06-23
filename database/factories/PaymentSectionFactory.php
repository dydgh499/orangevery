<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PaymentGateway;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentSection>
 */
class PaymentSectionFactory extends Factory
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
            'pg_id'     => PaymentGateway::all()->random()->id,
            'name'      => $this->faker->name,
            'trx_fee'   => $this->faker->randomFloat(4, 1, 2)/100,
        ];
    }
}
