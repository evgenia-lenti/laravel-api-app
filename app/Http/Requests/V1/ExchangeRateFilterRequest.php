<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'currency_to' => ['sometimes', 'string', 'size:3'],
            'currency_from' => ['sometimes', 'string', 'size:3'],
            'rate' => ['sometimes', 'numeric', 'min:0'],
            'retrieved_at' => ['sometimes', Rule::date()->format('Y-m-d H:i:s')],
        ];
    }

    public function messages(): array
    {
        return [
            'currency_to.string' => 'The currency_to field must consist of alphabet characters.',
            'currency_to.size' => 'The currency_to field must contain 3 digits.',
            'currency_from.string' => 'The currency_from field must consist of alphabet characters.',
            'currency_from.size' => 'The currency_from field must contain 3 digits.',
            'rate.numeric' => 'The rate field must be of a numeric value.',
            'rate.min' => 'The rate field value must be minimum 0.',
            'retrieved_at.date_format' => 'The retrieved at field must match the format Y-m-d H:i:s.'
        ];
    }
}
