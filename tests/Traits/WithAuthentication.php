<?php

namespace Tests\Traits;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

trait WithAuthentication
{
    protected User $user;

    protected function setUpUser(): void
    {
        //creates a user in the database (but doesn't authenticate it yet)
        $this->user = User::factory()->state([
            'email' => 'some@email.com',
            'password' => bcrypt('password'),
        ])->create();
    }

    protected function authenticateWithSanctum(): void
    {
        Sanctum::actingAs(
            $this->user,
            ['*']
        );
    }
}
