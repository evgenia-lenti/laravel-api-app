# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer 1|YOUR_SANCTUM_TOKEN"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

**Note:** All authentication tokens expire after 3 days, after which you'll need to obtain a new token.

## How to obtain a token

### Option 1: Register a new user (Recommended for new users)

Send a POST request to the `/api/v1/register` endpoint with the following JSON body:
```json
{
    "name": "Your Name",
    "email": "your@email.com",
    "password": "your_password",
    "password_confirmation": "your_password"
}
```

The response will include a token that you can immediately use for authentication:
```json
{
    "message": "User registered successfully",
    "user": {
        "name": "Your Name",
        "email": "your@email.com",
        "id": 1
    },
    "token": "1|your_token_here"
}
```

> **Note:** After registration, you are automatically logged in and can use the provided token for all authenticated requests. There is no need to perform a separate login unless you've logged out or your token has expired.

### Option 2: Login with existing credentials (Only if you've logged out or your token has expired)

Send a POST request to the `/api/v1/login` endpoint with the following JSON body:
```json
{
    "email": "your@email.com",
    "password": "your_password"
}
```

The response will include a new token:
```json
{
    "message": "Login successful",
    "user": {
        "name": "Your Name",
        "email": "your@email.com",
        "id": 1
    },
    "token": "1|your_token_here"
}
```


### Option 3: Generate a token for an existing user

You can use Laravel Tinker to create a token for an existing user:
```php
$user = \App\Models\User::where('email', 'some@email.com')->first();
$user->tokens()->delete(); // Delete existing tokens
$user->createToken('NewToken')->plainTextToken;
```
