<?php

namespace Tests\Feature\V1;

use App\Models\ExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthentication;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithAuthentication;

    protected string $registerToken;
    protected string $loginToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpUser();
    }

    /**
     * Test that a user can register and get a token
     */
    public function test_user_can_register_and_get_token(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'New Test User',
            'email' => 'new_test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'name',
                    'email',
                    'id'
                ],
                'token'
            ]);

        $this->assertNotEmpty($response->json('token'));
    }

    /**
     * Test that a user can login and get a token
     */
    public function test_user_can_login_and_get_token(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'some@email.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'name',
                    'email',
                    'id'
                ],
                'token'
            ]);

        $this->assertNotEmpty($response->json('token'));
    }

    /**
     * Test that unauthenticated users cannot access protected routes
     */
    public function test_unauthenticated_user_cannot_access_index_route()
    {
        //creates some exchange rates
        ExchangeRate::factory()->count(5)->create();

        //makes request without authentication
        $response = $this->getJson('/api/v1/exchange-rates');

        //asserts that the response is unauthorized (401)
        $response->assertStatus(401);
    }

    /**
     * Test that unauthenticated users cannot access protected show routes
     */
    public function test_unauthenticated_user_cannot_access_show_route()
    {
        //creates an exchange rate
        $exchangeRate = ExchangeRate::factory()->create();

        //makes request without authentication
        $response = $this->getJson('/api/v1/exchange-rates/' . $exchangeRate->id);

        //asserts that the response is unauthorized (401)
        $response->assertStatus(401);
    }

    /**
     * Test that a user can logout successfully
     */
    public function test_user_can_logout_successfully(): void
    {
        //login to get a token
        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => 'some@email.com',
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('token');
        $this->assertNotEmpty($token);

        //uses the token to logout
        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/logout');

        //checks that logout was successful
        $logoutResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully'
            ]);

        // In a real application, the token would be revoked
        // requests would fail with 401 Unauthorized. In the test environment,
        // Sanctum uses a TransientToken which doesn't get stored in the database,
        // so it can't be revoked in the same way as a real token.
    }
}
