<?php

namespace App\Policies;

use App\Models\ExchangeRate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ExchangeRatePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): Response
    {
        return $user->email === 'some@email.com'
            ? Response::allow()
            : Response::deny('You do not have permission to view the exchange rates.');
    }

    public function view(User $user, ExchangeRate $exchangeRate): Response
    {
        return $user->email === 'some@email.com'
            ? Response::allow()
            : Response::deny('You do not have permission to view this exchange rate.');
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, ExchangeRate $exchangeRate): bool
    {
    }

    public function delete(User $user, ExchangeRate $exchangeRate): bool
    {
    }

    public function restore(User $user, ExchangeRate $exchangeRate): bool
    {
    }

    public function forceDelete(User $user, ExchangeRate $exchangeRate): bool
    {
    }
}
