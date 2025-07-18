<?php

namespace Tests\Feature;

use App\Models\ExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ExchangeRateApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        //creates and authenticates a user for using in all methods in this class
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function testReturnsPaginatedListOfExchangedRates(): void
    {
        //creates 50 exchange rates
        ExchangeRate::factory()->count(50)->create();

        //makes a request at /api/v1/exchange-rates as the authenticated user
        $response = $this->get('/api/v1/exchange-rates');

        //checks the response
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [],
                'links' => [],
                'meta' => []
            ]);

        //asserts that the results are 15 per page, according to the laravel default pagination results
        $this->assertCount(15, $response->json('data'));
    }

    public function testFiltersExchangeRatesByCurrencyTo()
    {
        //creates 3 exchange rates with different currency_to values
        ExchangeRate::factory()->create(['currency_to' => 'USD']);
        ExchangeRate::factory()->create(['currency_to' => 'GBP']);
        ExchangeRate::factory()->create(['currency_to' => 'JPY']);

        //makes a request at /api/v1/exchange-rates as the authenticated user with a filter of
        //currency_to to be USD
        $response = $this->getJson('/api/v1/exchange-rates?currency_to=USD');

        //asserts that status = 200, that there is 1 result and that this 1 result has currency_to = USD
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('USD', $response->json('data.0.currency_to'));
    }

    public function testFiltersExchangeRatesByCurrencyFrom()
    {
        //creates 3 exchange rates with different currency_from values
        ExchangeRate::factory()->create(['currency_from' => 'EUR']);
        ExchangeRate::factory()->create(['currency_from' => 'GBP']);
        ExchangeRate::factory()->create(['currency_from' => 'JPY']);

        //makes a request at /api/v1/exchange-rates as the authenticated user with a filter of
        //currency_from to be EUR
        $response = $this->getJson('/api/v1/exchange-rates?currency_from=EUR');

        //asserts that status = 200, that there is 1 result and that this 1 result has currency_from = EUR
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('EUR', $response->json('data.0.currency_from'));
    }

    public function testFiltersExchangeRatesByRate()
    {
        //creates 3 exchange rates with different rate values
        ExchangeRate::factory()->create(['rate' => '1.25781803']);
        ExchangeRate::factory()->create(['rate' => '1.80854481']);
        ExchangeRate::factory()->create(['rate' => '-1']);

        //makes a request at /api/v1/exchange-rates as the authenticated user with a filter of
        //rate to be 1.25781803
        $response = $this->getJson('/api/v1/exchange-rates?rate=1.25781803');

        //asserts that status = 200, that there is 1 result and that this 1 result has rate = 1.25781803
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('1.25781803', $response->json('data.0.rate'));
    }

    public function testFiltersExchangeRatesByRetrievedAt()
    {
        //creates 3 exchange rates with different retrieved_at values
        ExchangeRate::factory()->create(['retrieved_at' => '2025-07-18 14:31:49']);
        ExchangeRate::factory()->create(['retrieved_at' => '2025-07-15 14:22:00']);
        ExchangeRate::factory()->create(['retrieved_at' => '2025-07-11 15:12:36']);

        //makes a request at /api/v1/exchange-rates as the authenticated user with a filter of
        //retrieved_at to be 2025-07-18 14:31:49
        $response = $this->getJson('/api/v1/exchange-rates?retrieved_at=2025-07-18 14:31:49');

        //asserts that status = 200, that there is 1 result and that this 1 result has retrieved_at = 2025-07-18 14:31:49
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('2025-07-18 14:31:49', $response->json('data.0.retrieved_at'));
    }

    public function testShowsSpecificExchangeRate()
    {
        //creates an exchange rates with currency_to = CHF
        $exchangeRate = ExchangeRate::factory()->create([
            'currency_to' => 'CHF',
        ]);

        //makes a request at /api/v1/exchange-rates/{exchangeRate} as the authenticated user
        $response = $this->getJson('/api/v1/exchange-rates/' . $exchangeRate->id);

        //asserts that an exchange rate is returned with the following fields
        $response->assertStatus(200)
            ->assertJson([
                'id' => $exchangeRate->id,
                'currency_from' => $exchangeRate->currency_from,
                'currency_to' => $exchangeRate->currency_to,
                'rate' => $exchangeRate->rate,
                'retrieved_at' => $exchangeRate->retrieved_at
            ]);
    }
}
