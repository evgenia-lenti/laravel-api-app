<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\ExchangeRateFilter;
use App\Http\Resources\V1\ExchangeRateCollection;
use App\Http\Resources\V1\ExchangeRateResource;
use App\Models\ExchangeRate;
use Throwable;

class ExchangeRateController extends Controller
{
    /**
     * Get a list of exchange rates
     *
     * Returns a paginated list of exchange rates from the European Central Bank.
     * The results can be filtered and sorted using query parameters.
     *
     * > **Important:** All filter parameters must use the bracket syntax: `filter[paramName]=value`
     * >
     * > Example URL with multiple filters: `/api/v1/exchange-rates?filter[currencyFrom]=EUR&filter[currencyTo]=USD,GBP&filter[sort]=-retrievedAt`
     *
     * @group Exchange Rates
     *
     * @queryParam filter[currencyFrom] string Filter by source currency (e.g., EUR). Can be a comma-separated list. Example: EUR
     * @queryParam filter[currencyTo] string Filter by target currency (e.g., USD). Can be a comma-separated list. Example: USD,GBP
     * @queryParam filter[exchangeRate] numeric Filter by exchange rate value. Can be a comma-separated list. Example: 1.0876
     * @queryParam filter[retrievedAt] string Filter by retrieval date (partial matching). Format: Y-m-d H:i:s. Example: 2025-07-19
     * @queryParam filter[sort] string Sort results by field. Prefix with - for descending order. Can be a comma-separated list. Example: -exchangeRate
     * @queryParam page integer Page number for pagination. Example: 1
     *
     * @response {
     *   "data": [
     *     {
     *       "id": 1,
     *       "currencyFrom": "EUR",
     *       "currencyTo": "USD",
     *       "exchangeRate": 1.0876,
     *       "retrievedAt": "2025-07-19 00:00:00"
     *     },
     *     {
     *       "id": 2,
     *       "currencyFrom": "EUR",
     *       "currencyTo": "JPY",
     *       "exchangeRate": 157.83,
     *       "retrievedAt": "2025-07-19 00:00:00"
     *     }
     *   ],
     *   "links": {
     *     "first": "http://localhost/api/v1/exchange-rates?page=1",
     *     "last": "http://localhost/api/v1/exchange-rates?page=1",
     *     "prev": null,
     *     "next": null
     *   },
     *   "meta": {
     *     "current_page": 1,
     *     "from": 1,
     *     "last_page": 1,
     *     "links": [
     *       {
     *         "url": null,
     *         "label": "&laquo; Previous",
     *         "active": false
     *       },
     *       {
     *         "url": "http://localhost/api/v1/exchange-rates?page=1",
     *         "label": "1",
     *         "active": true
     *       },
     *       {
     *         "url": null,
     *         "label": "Next &raquo;",
     *         "active": false
     *       }
     *     ],
     *     "path": "http://localhost/api/v1/exchange-rates",
     *     "per_page": 15,
     *     "to": 2,
     *     "total": 2
     *   }
     * }
     *
     * @throws Throwable
     */
    public function index(ExchangeRateFilter $filters)
    {
        $exchangeRates = ExchangeRate::filter($filters)->paginate();

        return new ExchangeRateCollection($exchangeRates);
    }

    /**
     * Get a specific exchange rate
     *
     * Returns detailed information about a specific exchange rate.
     *
     * @group Exchange Rates
     *
     * @urlParam exchangeRate integer required The ID of the exchange rate. Example: 1
     *
     * @response {
     *   "data": {
     *     "id": 1,
     *     "currencyFrom": "EUR",
     *     "currencyTo": "USD",
     *     "exchangeRate": 1.0876,
     *     "retrievedAt": "2025-07-19 00:00:00",
     *     "createdAt": "2025-07-19 10:30:00",
     *     "updatedAt": "2025-07-19 10:30:00"
     *   }
     * }
     */
    public function show(ExchangeRate $exchangeRate)
    {
        return new ExchangeRateResource($exchangeRate);
    }
}
