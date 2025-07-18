<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExchangeRateFilterRequest;
use App\Http\Resources\ExchangeRateCollection;
use App\Http\Resources\ExchangeRateResource;
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
        return response()->json(new ExchangeRateResource($exchangeRate));
    }
}
