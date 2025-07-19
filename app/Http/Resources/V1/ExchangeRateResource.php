<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'currencyFrom' => $this->currency_from,
            'currencyTo' => $this->currency_to,
            'exchangeRate' => $this->rate,
            'retrievedAt' => $this->retrieved_at,
            'createdAt' => $this->when(
                $request->routeIs('exchange-rates.show'),
                $this->created_at
            ),
            'updatedAt' => $this->when(
                $request->routeIs('exchange-rates.show'),
                $this->updated_at
            )
        ];
    }
}
