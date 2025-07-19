<?php

namespace App\Http\Filters\V1;

class ExchangeRateFilter extends QueryBuilder
{
    public function currencyFrom($value)
    {
        $values = is_array($value) ? $value : explode(',', $value);
        return $this->builder->whereIn('currency_from', $values);
    }

    public function currencyTo($value)
    {
        $values = is_array($value) ? $value : explode(',', $value);
        return $this->builder->whereIn('currency_to', $values);
    }

    public function exchangeRate($value)
    {
        $values = is_array($value) ? $value : explode(',', $value);
        return $this->builder->whereIn('rate', $values);
    }

    public function retrievedAt($value)
    {
        $values = is_array($value) ? $value : explode(',', $value);

        return $this->builder->where(function($query) use ($values) {
            foreach ($values as $date) {
                $query->orWhere('retrieved_at', 'like', '%' . $date . '%');
            }
        });
    }

    public function sort($value)
    {
        $columnMap = [
            'currencyTo' => 'currency_to',
            'currencyFrom' => 'currency_from',
            'exchangeRate' => 'rate',
            'retrievedAt' => 'retrieved_at'
        ];

        $allowedFields = array_keys($columnMap);
        $values = is_array($value) ? $value : [$value];

        foreach ($values as $sortField) {
            $direction = 'asc';
            $fieldName = $sortField;

            if (str_starts_with($sortField, '-')) {
                $direction = 'desc';
                $fieldName = substr($sortField, 1);
            }

            //validates that the field name is allowed
            if (!in_array($fieldName, $allowedFields)) {
                //skips invalid sort fields in order not to cause SQL error
                continue;
            }

            $column = $columnMap[$fieldName];
            $this->builder->orderBy($column, $direction);
        }

        return $this->builder;
    }
}
