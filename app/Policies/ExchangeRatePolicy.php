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
        //to be implemented along with a role system
        /*return $user->email === 'some@email.com'
            ? Response::allow()
            : Response::deny('You do not have permission to view the exchange rates.');*/

        return Response::allow();
    }

    public function view(User $user, ExchangeRate $exchangeRate): Response
    {
        //to be implemented along with a role system
        /*return $user->email === 'some@email.com'
            ? Response::allow()
            : Response::deny('You do not have permission to view this exchange rate.');*/

        return Response::allow();
    }

    public function create(User $user): Response
    {
        //to be implemented
        return Response::allow();
    }
}
