<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Price>
 */
class PriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'currency' => 'usd',
            'coin' => 'btc',
            'price' => 1234,
        ];
    }

    /**
     * Set a known creation date.
     *
     * @return static
     */
    public function knownDate()
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => Carbon::parse('2022-08-18 12:00:00'),
                'updated_at' => Carbon::parse('2022-08-18 12:00:00'),
            ];
        });
    }
}
