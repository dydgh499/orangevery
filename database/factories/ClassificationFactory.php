<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Brand;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ClassificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $custom_filter = [
            '에스에셋(단,수)',
            '영중소',
            '즉시출금',
            '야할라',
            '요식업',
            '제조업',
            '유통업',
            '레벨업',
        ];
        $terminals = [
            'M100',
            'Q2POS',
            '장비1',
            '장비2',
            '장비3',
            '장비4',
        ];
        $type = rand(0, 1);
        if($type == 0)
            $name = $terminals[array_rand($terminals)];
        else if($type == 1)
            $name = $custom_filter[array_rand($custom_filter)];

        return [
            'brand_id' => 1,
            'name' => $name,
            'type' => $type,
        ];
    }
}
