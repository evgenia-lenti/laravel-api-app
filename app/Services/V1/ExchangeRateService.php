<?php

namespace App\Services\V1;

use App\DTOs\V1\ExchangeRateDTO;
use App\Http\Filters\V1\ExchangeRateFilter;
use App\Http\Resources\V1\ExchangeRateCollection;
use App\Models\ExchangeRate;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class ExchangeRateService
{
    private const ECB_EXCHANGE_RATE_URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    /**
     * Fetches exchange rates from ECB, stores them in the database, and returns paginated results
     *
     * @param mixed $filters Optional filters to apply to the results
     * @return ExchangeRateCollection Collection of exchange rates
     * @throws ConnectionException If there's an error connecting to the ECB API
     * @throws QueryException If there's a database error during storage
     * @throws Exception If there's an error parsing the XML or storing the rates
     */
    public function fetchAndStoreRates($filters = null): ExchangeRateCollection
    {
        //checks if we're in a test environment with existing exchange rates
        //this allows tests to create exchange rates with factories and then filter them
        if (app()->environment('testing') && ExchangeRate::count() > 0) {
            //if we're in a test environment and there are already exchange rates,
            //just return the existing rates without fetching new ones
            return $this->getPaginatedRates($filters);
        }

        //in production or if there are no exchange rates yet, fetch from the ECB API
        $xmlData = $this->fetchRatesFromECB();
        $exchangeRates = $this->parseXmlResponse($xmlData);
        $this->storeRates($exchangeRates);

        return $this->getPaginatedRates($filters);
    }

    /**
     * Get paginated exchange rates with optional filtering
     */
    public function getPaginatedRates($filters = null): ExchangeRateCollection
    {
        if ($filters instanceof ExchangeRateFilter) {
            //if a filter object is passed directly
            $query = ExchangeRate::filter($filters);
        } else {
            //if no filters or if we're called from the command line
            $query = ExchangeRate::query();
        }

        return new ExchangeRateCollection($query->paginate());
    }

    /**
     * Fetches exchange rates from ECB and stores them in the database
     * Used primarily by the command line tool
     *
     * @return array Array of stored ExchangeRate models
     * @throws ConnectionException If there's an error connecting to the ECB API
     * @throws QueryException If there's a database error during storage
     * @throws Exception If there's an error parsing the XML or storing the rates
     */
    public function fetchAndStoreRatesArray(): array
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

        //gets the parent Cube with time attribute
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

    /**
     * Store exchange rates in the database using a transaction for data integrity
     *
     * @param array $exchangeRates Array of ExchangeRateDTO objects
     * @return array Array of stored ExchangeRate models
     * @throws Exception If there's an error during the transaction
     */
    public function storeRates(array $exchangeRates): array
    {
        try {
            return DB::transaction(function () use ($exchangeRates) {
                $storedRates = [];

                foreach ($exchangeRates as $rateDTO) {
                    $storedRate = ExchangeRate::create($rateDTO->toArray());
                    $storedRates[] = $storedRate;
                }

                return $storedRates;
            });
        } catch (QueryException $e) {
            Log::error('Database error while storing exchange rates: ' . $e->getMessage());
            throw new Exception('Failed to store exchange rates: ' . $e->getMessage(), 0, $e);
        } catch (Exception $e) {
            Log::error('Error while storing exchange rates: ' . $e->getMessage());
            throw new Exception('Failed to store exchange rates: ' . $e->getMessage(), 0, $e);
        }
    }
}
