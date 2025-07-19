# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer 1|YOUR_SANCTUM_TOKEN"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

You can obtain a token by running `php artisan db:seed` which creates a test user and displays the token, or by using Laravel Tinker to create a token for an existing user.
