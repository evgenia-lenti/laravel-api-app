<?php

namespace Tests\Feature\V1;

use App\Models\ExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Tests\Traits\WithAuthentication;

class ExchangeRateApiTest extends TestCase
{
    use RefreshDatabase, WithAuthentication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpUser();
    }

    public function test_returns_paginated_list_of_exchanged_rates(): void
    {
        //creates 50 exchange rates
        ExchangeRate::factory()->count(50)->create();

        //authenticates the user
        $this->authenticateWithSanctum();

        //makes a request at /api/v1/exchange-rates
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

    public function test_index_endpoint_fetches_and_stores_new_rates(): void
    {
        //clears the database
        ExchangeRate::query()->delete();

        //mocks the HTTP call to the ECB API
        Http::fake([
            'www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml' => Http::response(
                $this->getSampleXmlResponse(),
                200
            )
        ]);

        //authenticates the user
        $this->authenticateWithSanctum();

        //makes a request to the index endpoint
        $response = $this->get('/api/v1/exchange-rates');

        //checks that the response is successful
        $response->assertStatus(200);

        //checks that the rates were stored in the database
        $this->assertDatabaseHas('exchange_rates', [
            'currency_from' => 'EUR',
            'currency_to' => 'USD',
            'rate' => 1.0876
        ]);

        $this->assertDatabaseHas('exchange_rates', [
            'currency_from' => 'EUR',
            'currency_to' => 'JPY',
            'rate' => 157.83
        ]);

        //checks that we have exactly 2 records in the database
        $this->assertEquals(2, ExchangeRate::count());
    }

    /**
     * Get a sample XML response for testing
     */
    private function getSampleXmlResponse(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01" xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref">
    <gesmes:subject>Reference rates</gesmes:subject>
    <gesmes:Sender>
        <gesmes:name>European Central Bank</gesmes:name>
    </gesmes:Sender>
    <Cube>
        <Cube time="2025-07-19">
            <Cube currency="USD" rate="1.0876"/>
            <Cube currency="JPY" rate="157.83"/>
        </Cube>
    </Cube>
</gesmes:Envelope>
XML;
    }

    public function test_shows_specific_exchange_rate()
    {
        //creates an exchange rates with currency_to = CHF
        $exchangeRate = ExchangeRate::factory()->create([
            'currency_to' => 'CHF',
        ]);

        //authenticates the user
        $this->authenticateWithSanctum();

        //tests the endpoint
        $response = $this->getJson('/api/v1/exchange-rates/' . $exchangeRate->id);

        //creates base assertions on the fields that should always be returned
        $baseAssertions = [
            'id' => $exchangeRate->id,
            'currencyFrom' => $exchangeRate->currency_from,
            'currencyTo' => $exchangeRate->currency_to,
            'exchangeRate' => $exchangeRate->rate,
            'retrievedAt' => $exchangeRate->retrieved_at
        ];

        //checks if the current route is 'exchange-rates.show'
        $routeName = app('router')->currentRouteName();

        if ($routeName === 'exchange-rates.show') {
            //adds the timestamp fields to assertions if we are on the show route
            $baseAssertions['createdAt'] = $exchangeRate->created_at->toJSON();
            $baseAssertions['updatedAt'] = $exchangeRate->updated_at->toJSON();
        }

        //asserts the response
        $response->assertStatus(200)
            ->assertJson([
                'data' => $baseAssertions
            ]);
    }
}
