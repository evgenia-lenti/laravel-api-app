<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        //create a user for api usage
        $user = User::factory()->create([
            'email' => 'some@email.com',
            'password' => bcrypt('password'),
        ]);

        //create a token for this user for authentication
        $token = $user->createToken('AssessmentToken')->plainTextToken;

        // Print the token to console/log
        echo "\nTest user token: $token\n";
    }
}
