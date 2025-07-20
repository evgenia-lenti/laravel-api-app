<?php

namespace App\Http\Filters\V1;

use App\Http\Requests\V1\ExchangeRateFilterRequest;
use Illuminate\Database\Eloquent\Builder;

abstract class QueryBuilder
{
    protected Builder $builder;
    protected ExchangeRateFilterRequest $request;

    public function __construct(ExchangeRateFilterRequest $request)
    {
        $this->request = $request;
    }

    protected function filter($array): Builder
    {
        foreach ($array as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }

        return $this->builder;
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        //gets validated data from the request
        $validatedData = $this->request->validated();

        //processes filter parameters if they exist
        if (isset($validatedData['filter']) && is_array($validatedData['filter'])) {
            $this->filter($validatedData['filter']);
        }

        //processes direct parameters
        foreach ($validatedData as $key => $value) {
            // Skip the filter parameter as it's already processed
            if ($key === 'filter') {
                continue;
            }

            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }

        return $builder;
    }
}
