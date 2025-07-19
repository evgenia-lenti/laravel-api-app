<?php

namespace App\DTOs\V1;

use SimpleXMLElement;
use Illuminate\Support\Carbon;

class ExchangeRateDTO
{
    public function __construct(
        public string $currencyFrom,
        public string $currencyTo,
        public float $rate,
        public string $retrievedAt
    ) {}

    public static function fromXmlCube(SimpleXMLElement $cube, string $baseCurrency = 'EUR', string $cubeTime = null): self
    {
        //formats the cube time as a datetime using Carbon
        $retrievedAt = $cubeTime ? Carbon::parse($cubeTime)->toDateTimeString() : Carbon::now()->toDateTimeString();

        return new self(
            currencyFrom: $baseCurrency,
            currencyTo: (string) $cube['currency'],
            rate: (float) $cube['rate'],
            retrievedAt: $retrievedAt
        );
    }

    public function toArray(): array
    {
        return [
            'currency_from' => $this->currencyFrom,
            'currency_to' => $this->currencyTo,
            'rate' => $this->rate,
            'retrieved_at' => $this->retrievedAt
        ];
    }
}
