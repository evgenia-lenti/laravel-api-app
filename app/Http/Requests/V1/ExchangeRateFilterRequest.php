<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class ExchangeRateFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'filter' => ['sometimes', 'array'],
            'filter.currencyTo' => ['sometimes'],
            'filter.currencyTo.*' => ['string', 'size:3'],
            'filter.currencyFrom' => ['sometimes'],
            'filter.currencyFrom.*' => ['string', 'size:3'],
            'filter.exchangeRate' => ['sometimes'],
            'filter.exchangeRate.*' => ['numeric', 'min:0'],
            'filter.retrievedAt' => ['sometimes'],
            'filter.retrievedAt.*' => ['date_format:Y-m-d H:i:s'],

            'filter.sort' => ['sometimes'],
            'filter.sort.*' => [
                'string',
                function ($attribute, $value, $fail) {
                    if (str_starts_with($value, '-')) {
                        if (!in_array($value, ['-currencyTo', '-currencyFrom', '-exchangeRate', '-retrievedAt'])) {
                            $fail('The '.$attribute.' must be one of: currencyTo, currencyFrom, exchangeRate, retrievedAt, optionally prefixed with - for descending order.');
                        }
                    } else {
                        if (!in_array($value, ['currencyTo', 'currencyFrom', 'exchangeRate', 'retrievedAt'])) {
                            $fail('The '.$attribute.' must be one of: currencyTo, currencyFrom, exchangeRate, retrievedAt, optionally prefixed with - for descending order.');
                        }
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'filter.currencyTo.*.string' => 'Each value in the filter.currencyTo field must consist of alphabet characters.',
            'filter.currencyTo.*.size' => 'Each value in the filter.currencyTo field must contain 3 characters.',
            'filter.currencyFrom.*.string' => 'Each value in the filter.currencyFrom field must consist of alphabet characters.',
            'filter.currencyFrom.*.size' => 'Each value in the filter.currencyFrom field must contain 3 characters.',
            'filter.exchangeRate.*.numeric' => 'Each value in the filter.exchangeRate field must be of a numeric value.',
            'filter.exchangeRate.*.min' => 'Each value in the filter.exchangeRate field must be minimum 0.',
            'filter.retrievedAt.*.date_format' => 'Each value in the filter.retrievedAt field must match the format Y-m-d H:i:s.',
            'filter.sort.*.string' => 'Each value in the filter.sort field must be a string.',
            'filter.sort.*.in' => 'Each value in the filter.sort field must be one of: currencyTo, currencyFrom, exchangeRate, retrievedAt, optionally prefixed with - for descending order.'
        ];
    }
}
