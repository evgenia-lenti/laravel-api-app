<?php

namespace App\Services\V1;

use App\DTOs\V1\ExchangeRateDTO;
use App\Models\ExchangeRate;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class ExchangeRateService
{
    private const ECB_EXCHANGE_RATE_URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    /**
     * @throws Exception
     */
    public function fetchAndStoreRates(): array
    {
        $xmlData = $this->fetchRatesFromECB();
        $exchangeRates = $this->parseXmlResponse($xmlData);

        return $this->storeRates($exchangeRates);
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function fetchRatesFromECB(): string
    {
        $response = Http::get(self::ECB_EXCHANGE_RATE_URL);

        if ($response->failed()) {
            throw new Exception('Failed to fetch exchange rates from ECB: ' . $response->status());
        }

        return $response->body();
    }

    /**
     * @throws Exception
     */
    public function parseXmlResponse(string $xmlData): array
    {
        $xml = new SimpleXMLElement($xmlData);

        //registers the namespaces
        $namespaces = $xml->getNamespaces(true);

        //the default namespace is represented by an empty string key
        $defaultNs = $namespaces[''] ?? null;

        // Get the parent Cube with time attribute
        $cubeWithTime = $xml->children($defaultNs)->Cube->children()->Cube;
        $cubeTime = (string)$cubeWithTime['time'];

        //navigates to the Cube elements containing the exchange rates
        $cubes = $cubeWithTime->children();

        $exchangeRates = [];

        foreach ($cubes as $cube) {
            $dto = ExchangeRateDTO::fromXmlCube($cube, 'EUR', $cubeTime);
            $exchangeRates[] = $dto;
        }

        return $exchangeRates;
    }

    public function storeRates(array $exchangeRates): array
    {
        $storedRates = [];

        foreach ($exchangeRates as $rateDTO) {
            $storedRate = ExchangeRate::create($rateDTO->toArray());

            $storedRates[] = $storedRate;
        }

        return $storedRates;
    }
}
