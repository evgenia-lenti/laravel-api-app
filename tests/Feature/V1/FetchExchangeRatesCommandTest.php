<?php

namespace Tests\Feature\V1;

use App\Models\ExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchExchangeRatesCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the command successfully fetches and stores exchange rates
     */
    public function test_command_fetches_and_stores_rates(): void
    {
        //mocks the HTTP call to the ECB API
        Http::fake([
            'www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml' => Http::response(
                $this->getSampleXmlResponse(),
                200
            )
        ]);

        //executes the command
        $this->artisan('exchange-rates:fetch')
            ->expectsOutput('Fetching exchange rates from ECB...')
            ->expectsOutput('Successfully fetched and stored 2 exchange rates.')
            ->assertSuccessful();

        //asserts that the exchange rates were stored in the database
        $this->assertDatabaseHas('exchange_rates', [
            'currency_from' => 'EUR',
            'currency_to' => 'USD',
            'rate' => 1.0876,
        ]);

        $this->assertDatabaseHas('exchange_rates', [
            'currency_from' => 'EUR',
            'currency_to' => 'JPY',
            'rate' => 157.83,
        ]);

        //asserts that we have exactly 2 records in the database
        $this->assertEquals(2, ExchangeRate::count());
    }

    /**
     * Test that the command handles API failures gracefully
     */
    public function test_command_handles_api_failure(): void
    {
        //mocks the HTTP call to fail
        Http::fake([
            'www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml' => Http::response(
                'Internal Server Error',
                500
            )
        ]);

        //executes the command and expect it to fail
        $this->artisan('exchange-rates:fetch')
            ->expectsOutput('Fetching exchange rates from ECB...')
            ->expectsOutput('Failed to fetch exchange rates: Failed to fetch exchange rates from ECB: 500')
            ->assertFailed();

        //asserts that no exchange rates were stored
        $this->assertEquals(0, ExchangeRate::count());
    }

    /**
     * Test that the command handles connection exceptions
     */
    public function test_command_handles_connection_exception(): void
    {
        //mocks the HTTP call to throw a connection exception
        Http::fake(function () {
            throw new ConnectionException('Could not connect to host');
        });

        //executes the command and expect it to fail
        $this->artisan('exchange-rates:fetch')
            ->expectsOutput('Fetching exchange rates from ECB...')
            ->expectsOutput('Failed to fetch exchange rates: Could not connect to host')
            ->assertFailed();

        //asserts that no exchange rates were stored
        $this->assertEquals(0, ExchangeRate::count());
    }


    /**
     * Test that the command correctly parses the XML response
     */
    public function test_command_parses_xml_correctly(): void
    {
        //creates a custom XML response with multiple currencies
        $customXml = <<<XML
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
            <Cube currency="GBP" rate="0.8438"/>
            <Cube currency="CHF" rate="0.9632"/>
            <Cube currency="AUD" rate="1.6523"/>
        </Cube>
    </Cube>
</gesmes:Envelope>
XML;

        //mocks the HTTP call
        Http::fake([
            'www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml' => Http::response(
                $customXml,
                200
            )
        ]);

        //executes the command
        $this->artisan('exchange-rates:fetch')
            ->expectsOutput('Fetching exchange rates from ECB...')
            ->expectsOutput('Successfully fetched and stored 5 exchange rates.')
            ->assertSuccessful();

        //asserts that all 5 exchange rates were stored
        $this->assertEquals(5, ExchangeRate::count());

        //checks each currency
        $this->assertDatabaseHas('exchange_rates', ['currency_to' => 'USD', 'rate' => 1.0876]);
        $this->assertDatabaseHas('exchange_rates', ['currency_to' => 'JPY', 'rate' => 157.83]);
        $this->assertDatabaseHas('exchange_rates', ['currency_to' => 'GBP', 'rate' => 0.8438]);
        $this->assertDatabaseHas('exchange_rates', ['currency_to' => 'CHF', 'rate' => 0.9632]);
        $this->assertDatabaseHas('exchange_rates', ['currency_to' => 'AUD', 'rate' => 1.6523]);
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
