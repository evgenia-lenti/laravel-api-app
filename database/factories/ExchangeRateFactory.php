<?php

namespace Database\Factories;

use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ExchangeRateFactory extends Factory
{
    protected $model = ExchangeRate::class;

    public function definition(): array
    {
        return [
            'currency_from' => 'EUR',
            'currency_to' => $this->faker->currencyCode(),
            'rate' => $this->faker->randomFloat(8, 0.5, 2.0),
            'retrieved_at' => Carbon::now()->toDateTimeString(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
