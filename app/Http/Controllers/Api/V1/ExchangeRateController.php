<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ExchangeRateFilterRequest;
use App\Http\Resources\V1\ExchangeRateCollection;
use App\Http\Resources\V1\ExchangeRateResource;
use App\Models\ExchangeRate;
use Throwable;

class ExchangeRateController extends Controller
{
    /**
     * @throws Throwable
     */
    public function index(ExchangeRateFilterRequest $request)
    {
        $filters = $request->validated();

        $rates = ExchangeRate::filter($filters)->paginate();

        return new ExchangeRateCollection($rates);
    }

    public function show(ExchangeRate $exchangeRate)
    {
        return new ExchangeRateResource($exchangeRate);
    }
}
