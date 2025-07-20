<?php

namespace Tests\Feature\V1;

use App\Models\ExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthentication;

class ExchangeRateIndexFiltersTest extends TestCase
{
    use RefreshDatabase, WithAuthentication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpUser();
    }

    public function test_filters_exchange_rates_by_currency_to()
    {
        //creates 3 exchange rates with different currency_to values
        ExchangeRate::factory()->create(['currency_to' => 'USD']);
        ExchangeRate::factory()->create(['currency_to' => 'GBP']);
        ExchangeRate::factory()->create(['currency_to' => 'JPY']);

        //authenticates the user
        $this->authenticateWithSanctum();

        //makes a request at /api/v1/exchange-rates with a filter of
        //currency_to to be USD
        $response = $this->getJson('/api/v1/exchange-rates?filter[currencyTo]=USD');

        //asserts that status = 200, that there is 1 result and that this 1 result has currency_to = USD
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('USD', $response->json('data.0.currencyTo'));
    }

    public function test_filters_exchange_rates_by_multiple_currency_to()
    {
        //creates 3 exchange rates with different currency_to values
        ExchangeRate::factory()->create(['currency_to' => 'USD']);
        ExchangeRate::factory()->create(['currency_to' => 'GBP']);
        ExchangeRate::factory()->create(['currency_to' => 'JPY']);

        //authenticates the user
        $this->authenticateWithSanctum();

        //makes a request at /api/v1/exchange-rates with a filter of
        //currency_to to be USD or GBP
        $response = $this->getJson('/api/v1/exchange-rates?filter[currencyTo][]=USD&filter[currencyTo][]=GBP');

        //asserts that status = 200, that there are 2 results
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        //gets the currency_to values from the response
        $currencyToValues = collect($response->json('data'))->pluck('currencyTo')->toArray();

        //asserts that the response contains both USD and GBP
        $this->assertContains('USD', $currencyToValues);
        $this->assertContains('GBP', $currencyToValues);
        $this->assertNotContains('JPY', $currencyToValues);
    }

    public function test_filters_exchange_rates_by_currency_from()
    {
        //creates 3 exchange rates with different currency_from values
        ExchangeRate::factory()->create(['currency_from' => 'EUR']);
        ExchangeRate::factory()->create(['currency_from' => 'GBP']);
        ExchangeRate::factory()->create(['currency_from' => 'JPY']);

        //authenticates the user
        $this->authenticateWithSanctum();

        //makes a request at /api/v1/exchange-rates with a filter of
        //currency_from to be EUR
        $response = $this->getJson('/api/v1/exchange-rates?filter[currencyFrom]=EUR');

        //asserts that status = 200, that there is 1 result and that this 1 result has currency_from = EUR
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('EUR', $response->json('data.0.currencyFrom'));
    }

    public function test_filters_exchange_rates_by_multiple_currency_from()
    {
        //creates 3 exchange rates with different currency_from values
        ExchangeRate::factory()->create(['currency_from' => 'EUR']);
        ExchangeRate::factory()->create(['currency_from' => 'GBP']);
        ExchangeRate::factory()->create(['currency_from' => 'JPY']);

        //authenticates the user
        $this->authenticateWithSanctum();

        //makes a request at /api/v1/exchange-rates with a filter of
        //currency_from to be EUR or GBP
        $response = $this->getJson('/api/v1/exchange-rates?filter[currencyFrom][]=EUR&filter[currencyFrom][]=GBP');

        //asserts that status = 200, that there are 2 results
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        //gets the currency_from values from the response
        $currencyFromValues = collect($response->json('data'))->pluck('currencyFrom')->toArray();

        //asserts that the response contains both EUR and GBP
        $this->assertContains('EUR', $currencyFromValues);
        $this->assertContains('GBP', $currencyFromValues);
        $this->assertNotContains('JPY', $currencyFromValues);
    }

    public function test_filters_exchange_rates_by_rate()
    {
        //creates 3 exchange rates with different rate values
        ExchangeRate::factory()->create(['rate' => '1.25781803']);
        ExchangeRate::factory()->create(['rate' => '1.80854481']);
        ExchangeRate::factory()->create(['rate' => '-1']);

        //authenticates the user
        $this->authenticateWithSanctum();

        //makes a request at /api/v1/exchange-rates with a filter of
        //rate to be 1.25781803
        $response = $this->getJson('/api/v1/exchange-rates?filter[exchangeRate]=1.25781803');

        //asserts that status = 200, that there is 1 result and that this 1 result has rate = 1.25781803
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('1.25781803', $response->json('data.0.exchangeRate'));
    }

    public function test_filters_exchange_rates_by_multiple_rates()
    {
        //creates 3 exchange rates with different rate values
        ExchangeRate::factory()->create(['rate' => '1.25781803']);
        ExchangeRate::factory()->create(['rate' => '1.80854481']);
        ExchangeRate::factory()->create(['rate' => '-1']);

        //verifies that the rates were created correctly
        $this->assertDatabaseHas('exchange_rates', ['rate' => '1.25781803']);
        $this->assertDatabaseHas('exchange_rates', ['rate' => '1.80854481']);
        $this->assertDatabaseHas('exchange_rates', ['rate' => '-1']);

        //authenticates the user
        $this->authenticateWithSanctum();

        //makes a request at /api/v1/exchange-rates with a filter of
        //rate to be 1.25781803 or 1.80854481
        $response = $this->getJson('/api/v1/exchange-rates?filter[exchangeRate][]=1.25781803&filter[exchangeRate][]=1.80854481');

        //asserts that status = 200, that there are 2 results
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        //gets the rate values from the response
        $rateValues = collect($response->json('data'))->pluck('exchangeRate')->toArray();

        //asserts that the response contains both rates using numeric values
        $this->assertContains(1.25781803, $rateValues);
        $this->assertContains(1.80854481, $rateValues);
        $this->assertNotContains(-1, $rateValues);
    }

    public function test_filters_exchange_rates_by_retrieved_at()
    {
        //creates 3 exchange rates with different retrieved_at values
        ExchangeRate::factory()->create(['retrieved_at' => '2025-07-18 14:31:49']);
        ExchangeRate::factory()->create(['retrieved_at' => '2025-07-15 14:22:00']);
        ExchangeRate::factory()->create(['retrieved_at' => '2025-07-11 15:12:36']);

        //authenticates the user
        $this->authenticateWithSanctum();

        //makes a request at /api/v1/exchange-rates with a filter of
        //retrieved_at to be 2025-07-18 14:31:49
        $response = $this->getJson('/api/v1/exchange-rates?filter[retrievedAt]=2025-07-18 14:31:49');

        //asserts that status = 200, that there is 1 result and that this 1 result has retrieved_at = 2025-07-18 14:31:49
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('2025-07-18 14:31:49', $response->json('data.0.retrievedAt'));
    }

    public function test_filters_exchange_rates_by_multiple_retrieved_at()
    {
        //creates 3 exchange rates with different retrieved_at values
        ExchangeRate::factory()->create(['retrieved_at' => '2025-07-18 14:31:49']);
        ExchangeRate::factory()->create(['retrieved_at' => '2025-07-15 14:22:00']);
        ExchangeRate::factory()->create(['retrieved_at' => '2025-07-11 15:12:36']);

        //authenticates the user
        $this->authenticateWithSanctum();

        //makes a request at /api/v1/exchange-rates with a filter of
        //retrieved_at to be 2025-07-18 14:31:49 or 2025-07-15 14:22:00
        $response = $this->getJson('/api/v1/exchange-rates?filter[retrievedAt][]=2025-07-18 14:31:49&filter[retrievedAt][]=2025-07-15 14:22:00');

        //asserts that status = 200, that there are 2 results
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        //gets the retrieved_at values from the response
        $retrievedAtValues = collect($response->json('data'))->pluck('retrievedAt')->toArray();

        //asserts that the response contains both dates
        $this->assertContains('2025-07-18 14:31:49', $retrievedAtValues);
        $this->assertContains('2025-07-15 14:22:00', $retrievedAtValues);
        $this->assertNotContains('2025-07-11 15:12:36', $retrievedAtValues);
    }

    public function test_sorts_exchange_rates_ascending()
    {
        //creates exchange rates with different currency_to values in non-alphabetical order
        ExchangeRate::factory()->create(['currency_to' => 'USD']);
        ExchangeRate::factory()->create(['currency_to' => 'EUR']);
        ExchangeRate::factory()->create(['currency_to' => 'GBP']);

        //authenticates the user
        $this->authenticateWithSanctum();

        //makes a request with filter[sort]=currencyTo parameter (ascending sort)
        $response = $this->getJson('/api/v1/exchange-rates?filter[sort]=currencyTo');

        //asserts response status
        $response->assertStatus(200);

        //gets the currency_to values from the response in the order they were returned
        $currencyToValues = collect($response->json('data'))->pluck('currencyTo')->toArray();

        //asserts that the values are sorted in ascending order
        $this->assertEquals(['EUR', 'GBP', 'USD'], $currencyToValues);
    }

    public function test_sorts_exchange_rates_descending()
    {
        //creates exchange rates with different currency_to values in non-alphabetical order
        ExchangeRate::factory()->create(['currency_to' => 'USD']);
        ExchangeRate::factory()->create(['currency_to' => 'EUR']);
        ExchangeRate::factory()->create(['currency_to' => 'GBP']);

        //authenticates the user
        $this->authenticateWithSanctum();

        //makes a request with filter[sort]=-currencyTo parameter (descending sort)
        $response = $this->getJson('/api/v1/exchange-rates?filter[sort]=-currencyTo');

        //asserts response status
        $response->assertStatus(200);

        //gets the currency_to values from the response in the order they were returned
        $currencyToValues = collect($response->json('data'))->pluck('currencyTo')->toArray();

        //asserts that the values are sorted in descending order
        $this->assertEquals(['USD', 'GBP', 'EUR'], $currencyToValues);
    }

    public function test_sorts_exchange_rates_by_different_fields()
    {
        //create exchange rates with different values
        ExchangeRate::factory()->create([
            'currency_from' => 'USD',
            'currency_to' => 'EUR',
            'rate' => 1.2,
            'retrieved_at' => '2025-07-15 10:00:00'
        ]);
        ExchangeRate::factory()->create([
            'currency_from' => 'EUR',
            'currency_to' => 'GBP',
            'rate' => 0.9,
            'retrieved_at' => '2025-07-16 10:00:00'
        ]);
        ExchangeRate::factory()->create([
            'currency_from' => 'GBP',
            'currency_to' => 'USD',
            'rate' => 1.5,
            'retrieved_at' => '2025-07-14 10:00:00'
        ]);

        //authenticates the user
        $this->authenticateWithSanctum();

        //tests sorting by currency_from (ascending)
        $response = $this->getJson('/api/v1/exchange-rates?filter[sort]=currencyFrom');
        $currencyFromValues = collect($response->json('data'))->pluck('currencyFrom')->toArray();
        $this->assertEquals(['EUR', 'GBP', 'USD'], $currencyFromValues);

        //tests sorting by exchangeRate (descending)
        $response = $this->getJson('/api/v1/exchange-rates?filter[sort]=-exchangeRate');
        $rateValues = collect($response->json('data'))->pluck('exchangeRate')->toArray();
        $this->assertEquals([1.5, 1.2, 0.9], $rateValues);

        //tests sorting by retrievedAt (ascending)
        $response = $this->getJson('/api/v1/exchange-rates?filter[sort]=retrievedAt');
        $retrievedAtValues = collect($response->json('data'))->pluck('retrievedAt')->toArray();
        $this->assertEquals(['2025-07-14 10:00:00', '2025-07-15 10:00:00', '2025-07-16 10:00:00'], $retrievedAtValues);
    }

    public function test_sorts_exchange_rates_by_multiple_fields()
    {
        //creates exchange rates with different values
        ExchangeRate::factory()->create([
            'currency_from' => 'USD',
            'currency_to' => 'EUR',
            'rate' => 1.2,
            'retrieved_at' => '2025-07-15 10:00:00'
        ]);
        ExchangeRate::factory()->create([
            'currency_from' => 'EUR',
            'currency_to' => 'GBP',
            'rate' => 0.9,
            'retrieved_at' => '2025-07-16 10:00:00'
        ]);
        ExchangeRate::factory()->create([
            'currency_from' => 'GBP',
            'currency_to' => 'USD',
            'rate' => 1.5,
            'retrieved_at' => '2025-07-14 10:00:00'
        ]);

        //authenticates the user
        $this->authenticateWithSanctum();

        //tests sorting by multiple fields (first by currency_from ascending, then by rate descending)
        $response = $this->getJson('/api/v1/exchange-rates?filter[sort][]=currencyFrom&filter[sort][]=-exchangeRate');

        //asserts response status
        $response->assertStatus(200);

        //gets the data from the response
        $data = $response->json('data');

        //asserts that the first item has currency_from = 'EUR'
        $this->assertEquals('EUR', $data[0]['currencyFrom']);

        //asserts that the second item has currency_from = 'GBP'
        $this->assertEquals('GBP', $data[1]['currencyFrom']);

        //asserts that the third item has currency_from = 'USD'
        $this->assertEquals('USD', $data[2]['currencyFrom']);

        //authenticates the user again for the second test
        $this->authenticateWithSanctum();

        //tests another combination (first by retrievedAt descending, then by currencyTo ascending)
        $response = $this->getJson('/api/v1/exchange-rates?filter[sort][]=-retrievedAt&filter[sort][]=currencyTo');

        //asserts response status
        $response->assertStatus(200);

        //gets the data from the response
        $data = $response->json('data');

        //asserts that the first item has retrieved_at = '2025-07-16 10:00:00'
        $this->assertEquals('2025-07-16 10:00:00', $data[0]['retrievedAt']);

        //asserts that the second item has retrieved_at = '2025-07-15 10:00:00'
        $this->assertEquals('2025-07-15 10:00:00', $data[1]['retrievedAt']);

        //asserts that the third item has retrieved_at = '2025-07-14 10:00:00'
        $this->assertEquals('2025-07-14 10:00:00', $data[2]['retrievedAt']);
    }

    public function test_filters_exchange_rates_with_filter_array_single_value()
    {
        //create exchange rates with different values
        ExchangeRate::factory()->create(['currency_to' => 'USD']);
        ExchangeRate::factory()->create(['currency_to' => 'GBP']);
        ExchangeRate::factory()->create(['currency_to' => 'JPY']);

        //authenticates the user
        $this->authenticateWithSanctum();

        //tests filtering with a single value in the filter array
        $response = $this->getJson('/api/v1/exchange-rates?filter[currencyTo]=USD');

        //asserts response status
        $response->assertStatus(200);

        //asserts that there is 1 result and it has currencyTo = 'USD'
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('USD', $response->json('data.0.currencyTo'));
    }

    public function test_filters_exchange_rates_with_filter_array_multiple_values()
    {
        //creates exchange rates with different values
        ExchangeRate::factory()->create(['currency_from' => 'EUR']);
        ExchangeRate::factory()->create(['currency_from' => 'GBP']);
        ExchangeRate::factory()->create(['currency_from' => 'JPY']);

        //authenticates the user
        $this->authenticateWithSanctum();

        //tests filtering with multiple values in the filter array
        $response = $this->getJson('/api/v1/exchange-rates?filter[currencyFrom][]=EUR&filter[currencyFrom][]=GBP');

        //asserts response status
        $response->assertStatus(200);

        //asserts that there are 2 results
        $this->assertCount(2, $response->json('data'));

        //gets the currency_from values from the response
        $currencyFromValues = collect($response->json('data'))->pluck('currencyFrom')->toArray();

        //asserts that the response contains both EUR and GBP
        $this->assertContains('EUR', $currencyFromValues);
        $this->assertContains('GBP', $currencyFromValues);
        $this->assertNotContains('JPY', $currencyFromValues);
    }

    public function test_filters_exchange_rates_with_multiple_filter_array_parameters()
    {
        //creates exchange rates with different values
        ExchangeRate::factory()->create([
            'currency_from' => 'EUR',
            'currency_to' => 'USD',
            'rate' => 1.2
        ]);
        ExchangeRate::factory()->create([
            'currency_from' => 'EUR',
            'currency_to' => 'GBP',
            'rate' => 0.9
        ]);
        ExchangeRate::factory()->create([
            'currency_from' => 'GBP',
            'currency_to' => 'USD',
            'rate' => 1.5
        ]);

        //authenticates the user
        $this->authenticateWithSanctum();

        //tests filtering with multiple filter array parameters
        $response = $this->getJson('/api/v1/exchange-rates?filter[currencyFrom]=EUR&filter[currencyTo]=USD');

        //asserts response status
        $response->assertStatus(200);

        //asserts that there is 1 result
        $this->assertCount(1, $response->json('data'));

        //asserts that the result has currency_from = 'EUR' and currency_to = 'USD'
        $this->assertEquals('EUR', $response->json('data.0.currencyFrom'));
        $this->assertEquals('USD', $response->json('data.0.currencyTo'));
    }

    public function test_filters_exchange_rates_with_multiple_filter_array_fields()
    {
        //creates exchange rates with different values
        ExchangeRate::factory()->create([
            'currency_from' => 'EUR',
            'currency_to' => 'USD',
            'rate' => 1.2,
            'retrieved_at' => '2025-07-15 10:00:00'
        ]);
        ExchangeRate::factory()->create([
            'currency_from' => 'EUR',
            'currency_to' => 'GBP',
            'rate' => 0.9,
            'retrieved_at' => '2025-07-16 10:00:00'
        ]);
        ExchangeRate::factory()->create([
            'currency_from' => 'GBP',
            'currency_to' => 'USD',
            'rate' => 1.5,
            'retrieved_at' => '2025-07-14 10:00:00'
        ]);

        //authenticates the user
        $this->authenticateWithSanctum();

        //tests filtering with multiple fields in the filter array
        $response = $this->getJson('/api/v1/exchange-rates?filter[currencyFrom][]=EUR&filter[currencyTo][]=USD&filter[currencyTo][]=GBP');

        //asserts response status
        $response->assertStatus(200);

        //asserts that there are 2 results
        $this->assertCount(2, $response->json('data'));

        //gets the currency_to values from the response
        $currencyToValues = collect($response->json('data'))->pluck('currencyTo')->toArray();

        //asserts that the response contains both USD and GBP
        $this->assertContains('USD', $currencyToValues);
        $this->assertContains('GBP', $currencyToValues);

        //asserts that all results have currency_from = 'EUR'
        $currencyFromValues = collect($response->json('data'))->pluck('currencyFrom')->toArray();
        $this->assertEquals(['EUR', 'EUR'], $currencyFromValues);
    }
}
