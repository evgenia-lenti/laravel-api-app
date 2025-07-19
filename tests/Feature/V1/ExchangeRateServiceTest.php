<?php

namespace Tests\Feature\V1;

use App\DTOs\V1\ExchangeRateDTO;
use App\Models\ExchangeRate;
use App\Services\V1\ExchangeRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRateServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExchangeRateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ExchangeRateService::class);
    }

    /**
     * Test that the service can successfully fetch exchange rates from ECB
     */
    public function test_service_can_fetch_exchange_rates_from_ecb(): void
    {
        //mocks the HTTP call to the ECB API
        Http::fake([
            'www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml' => Http::response(
                $this->getSampleXmlResponse(),
                200
            )
        ]);

        //executes the service method
        $xmlData = $this->service->fetchRatesFromECB();

        //asserts the response contains expected data
        $this->assertNotEmpty($xmlData);
        $this->assertStringContainsString('<Cube currency="USD"', $xmlData);
        $this->assertStringContainsString('<Cube currency="JPY"', $xmlData);
    }

    /**
     * Test that the service can parse XML response correctly
     */
    public function test_service_can_parse_xml_response_correctly(): void
    {
        //executes the service method
        $rates = $this->service->parseXmlResponse($this->getSampleXmlResponse());

        //asserts the parsed data is correct
        $this->assertCount(2, $rates);
        $this->assertInstanceOf(ExchangeRateDTO::class, $rates[0]);
        $this->assertEquals('EUR', $rates[0]->currencyFrom);
        $this->assertEquals('USD', $rates[0]->currencyTo);
        $this->assertEquals(1.0876, $rates[0]->rate);

        $this->assertInstanceOf(ExchangeRateDTO::class, $rates[1]);
        $this->assertEquals('EUR', $rates[1]->currencyFrom);
        $this->assertEquals('JPY', $rates[1]->currencyTo);
        $this->assertEquals(157.83, $rates[1]->rate);
    }

    /**
     * Test that the service can store exchange rates in the database
     */
    public function test_service_can_store_exchange_rates_in_database(): void
    {
        //creates DTOs to store
        $dtos = [
            new ExchangeRateDTO(
                currencyFrom: 'EUR',
                currencyTo: 'USD',
                rate: 1.0876,
                retrievedAt: date('Y-m-d H:i:s')
            ),
            new ExchangeRateDTO(
                currencyFrom: 'EUR',
                currencyTo: 'JPY',
                rate: 157.83,
                retrievedAt: date('Y-m-d H:i:s')
            )
        ];

        //executes the service method
        $storedRates = $this->service->storeRates($dtos);

        //asserts the rates were stored correctly
        $this->assertCount(2, $storedRates);
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

        //asserts that the returned models have the correct data
        $this->assertEquals('EUR', $storedRates[0]->currency_from);
        $this->assertEquals('USD', $storedRates[0]->currency_to);
        $this->assertEquals(1.0876, $storedRates[0]->rate);
    }


    /**
     * Test the complete fetch and store process
     */
    public function test_service_can_fetch_and_store_rates(): void
    {
        //mocks the HTTP call to the ECB API
        Http::fake([
            'www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml' => Http::response(
                $this->getSampleXmlResponse(),
                200
            )
        ]);

        //executes the service method
        $storedRates = $this->service->fetchAndStoreRates();

        //asserts the rates were stored correctly
        $this->assertCount(2, $storedRates);
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

        //asserts that we have exactly 2 records in the database
        $this->assertEquals(2, ExchangeRate::count());
    }

    /**
     * Test that the service handles API failures gracefully
     */
    public function test_service_handles_api_failure(): void
    {
        //mocks the HTTP call to fail
        Http::fake([
            'www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml' => Http::response(
                'Internal Server Error',
                500
            )
        ]);

        //expects an exception when calling the service
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to fetch exchange rates from ECB: 500');

        //executes the service method
        $this->service->fetchAndStoreRates();

        //asserts that no exchange rates were stored
        $this->assertEquals(0, ExchangeRate::count());
    }

    /**
     * Test that the service handles connection exceptions
     */
    public function test_service_handles_connection_exception(): void
    {
        //mocks the HTTP call to throw a connection exception
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Could not connect to host');
        });

        //expects an exception when calling the service
        $this->expectException(\Illuminate\Http\Client\ConnectionException::class);
        $this->expectExceptionMessage('Could not connect to host');

        //executes the service method
        $this->service->fetchAndStoreRates();

        //asserts that no exchange rates were stored
        $this->assertEquals(0, ExchangeRate::count());
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
}
