# Laravel API App - Exchange Rate Service

This project is a Laravel-based application that fetches and stores exchange rates from the European Central Bank (ECB). It provides an API to access these exchange rates.

## Project Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- Laravel 12.x
- Laragon or similar local development environment
- SimpleXML PHP extension

### Installation Steps

1. **Clone the repository**

   ```bash
   git clone https://github.com/evgenia-lenti/laravel-api-app.git
   cd laravel-api-app
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Environment setup**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**

   The project is configured to use SQLite by default. If you want to use another database:
   
   - Update the `.env` file with your database credentials
   - Uncomment and modify the database configuration in `.env`

   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=database_name
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations**

   ```bash
   php artisan migrate
   ```


## Project Structure

### Key Components

- **ExchangeRateDTO**: Data Transfer Object for exchange rates
- **ExchangeRateService**: Service for fetching and parsing exchange rates from ECB
- **ExchangeRateController**: API controller for exchange rates
- **FetchExchangeRatesCommand**: Artisan command to fetch exchange rates

### API Endpoints

- `GET /api/v1/exchange-rates`: List all exchange rates (requires authentication)
  - This endpoint fetches the latest exchange rates from the ECB API every time it's called
  - The rates are stored in the database and then returned as a paginated response
  - You can filter and sort the results using query parameters (see below)
- `GET /api/v1/exchange-rates/{exchangeRate}`: Get a specific exchange rate (requires authentication)
- `POST /api/v1/logout`: Logout and revoke the current token (requires authentication)

### Filtering and Sorting

The Exchange Rate API supports powerful filtering and sorting capabilities. To use these features, you must format your query parameters using the bracket syntax:

```
filter[parameterName]=value
```

#### Available Filters

- `filter[currencyFrom]`: Filter by source currency (e.g., EUR)
- `filter[currencyTo]`: Filter by target currency (e.g., USD, GBP)
- `filter[exchangeRate]`: Filter by exchange rate value
- `filter[retrievedAt]`: Filter by retrieval date (supports partial matching)

#### Sorting

To sort results, use the `filter[sort]` parameter:

- Ascending order: `filter[sort]=fieldName`
- Descending order: `filter[sort]=-fieldName` (note the minus sign prefix)

Available sort fields: `currencyFrom`, `currencyTo`, `exchangeRate`, `retrievedAt`

#### Combining Multiple Filters

You can combine multiple filters in a single request:

```
/api/v1/exchange-rates?filter[currencyFrom]=EUR&filter[currencyTo]=USD,GBP&filter[sort]=-retrievedAt
```

This example:
1. Filters for exchange rates from EUR
2. Filters for exchange rates to either USD or GBP
3. Sorts results by retrieval date in descending order (newest first)

### Authentication

The API endpoints are protected with Laravel Sanctum. All authentication tokens expire after 3 days, after which you'll need to obtain a new token.

To access the protected endpoints:

#### Register a new user (Recommended for Postman)

1. Send a POST request to the `/api/v1/register` endpoint with the following JSON body:
   ```json
   {
       "name": "Your Name",
       "email": "your@email.com",
       "password": "your_password",
       "password_confirmation": "your_password"
   }
   ```

2. The response will include a token that you can immediately use for authentication:
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

3. Include this token in your API requests using the Authorization header:
   ```
   Authorization: Bearer YOUR_TOKEN_HERE
   ```

> **Note:** After registration, you are automatically logged in and can use the provided token for all authenticated requests. There is no need to perform a separate login unless you've logged out or your token has expired.

#### Login with existing credentials

This step is only necessary if you've previously logged out or your token has expired.

1. Send a POST request to the `/api/v1/login` endpoint with the following JSON body:
   ```json
   {
       "email": "your@email.com",
       "password": "your_password"
   }
   ```

2. The response will include a new token:
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

3. Include this token in your API requests using the Authorization header:
   ```
   Authorization: Bearer YOUR_TOKEN_HERE
   ```

#### Logging Out and Revoking Tokens

To logout and revoke your current token:

1. Send a POST request to the `/api/v1/logout` endpoint with an empty body
2. Include your token in the Authorization header:
   ```
   Authorization: Bearer YOUR_TOKEN_HERE
   ```
3. The response will confirm successful logout:
   ```json
   {
       "message": "Logged out successfully"
   }
   ```
4. After logout, the token is revoked and can no longer be used for authentication


## Fetching Exchange Rates

The application includes a command to fetch exchange rates from the European Central Bank:

```bash
php artisan exchange-rates:fetch
```

This command:
- Connects to the ECB's XML feed
- Parses the exchange rate data
- Stores the rates in your database

You can run this command manually whenever you need to fetch and store the exchange rates in your application.

## API Documentation

The API is documented using [Scribe](https://scribe.knuckles.wtf/laravel/), which provides interactive documentation for all endpoints.

### Accessing the Documentation

After setting up the project, you can access the API documentation at:

```
http://localhost:8000/docs
```

The documentation includes:

- Detailed information about all available endpoints
- Request parameters and their validation rules
- Example requests and responses
- Authentication instructions
- A Postman collection that you can import to test the API

### Generating Updated Documentation

If you make changes to the API, you can regenerate the documentation with:

```bash
php artisan scribe:generate
```

## Testing

Run the test suite to ensure everything is working correctly:

```bash
php artisan test
```

The project includes several test classes:
- `ExchangeRateApiTest`: Tests the exchange rate API endpoints
- `AuthenticationTest`: Tests the authentication API endpoints
- `FetchExchangeRatesCommandTest`: Tests the Artisan command
- `ExchangeRateServiceTest`: Tests the service layer

### Test Authentication

The project uses a centralized approach for authentication in tests through the `WithAuthentication` trait located in `tests/Traits/WithAuthentication.php`. This trait:

- Provides a common way to create and authenticate test users
- Reduces code duplication across test classes
- Ensures consistent authentication behavior

To use authentication in your tests:

```php
use Tests\Traits\WithAuthentication;

class YourTest extends TestCase
{
    use RefreshDatabase, WithAuthentication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpUser();
    }

    public function test_example(): void
    {
        // Authenticate the user when needed
        $this->authenticateWithSanctum();
        
        // Make authenticated requests
        $response = $this->get('/api/v1/protected-endpoint');
        
        // Assert response
        $response->assertStatus(200);
    }
}
```


## Troubleshooting

- **API Authentication Issues**: Ensure Sanctum is properly configured
- **Exchange Rate Fetch Failures**: Check network connectivity to ECB
- **Database Connection Issues**: Verify database credentials in `.env`
- **API Documentation Issues**: If the documentation is not displaying correctly, try regenerating it with `php artisan scribe:generate`

