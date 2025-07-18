<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    public function run(): void
    {
        //create fake exchange rates
        ExchangeRate::factory()->count(50)->create();
    }
}
