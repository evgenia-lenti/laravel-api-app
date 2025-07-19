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

6. **Seed the database**

   Run the database seeders to create a test user with an API token:

   ```bash
   php artisan db:seed
   ```

   This command will:
   - Create a test user with email `some@email.com` and password `password`
   - Generate an API token for this user
   - Display the token in the console output

   Make note of the token displayed in the console, as you'll need it to authenticate API requests.
   
   Example token output:
   ```
   Test user token: 1|laravel_sanctum_hashed_token_example
   ```

7. **Fetch exchange rates**

   To fetch exchange rates from the European Central Bank, run:

   ```bash
   php artisan exchange-rates:fetch
   ```

   This command will fetch the latest exchange rates and store them in your database. You can run this command manually whenever you need to fetch and store the exchange rates.


## Project Structure

### Key Components

- **ExchangeRateDTO**: Data Transfer Object for exchange rates
- **ExchangeRateService**: Service for fetching and parsing exchange rates from ECB
- **ExchangeRateController**: API controller for exchange rates
- **FetchExchangeRatesCommand**: Artisan command to fetch exchange rates

### API Endpoints

- `GET /api/v1/exchange-rates`: List all exchange rates (requires authentication)
- `GET /api/v1/exchange-rates/{exchangeRate}`: Get a specific exchange rate (requires authentication)

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

The API endpoints are protected with Laravel Sanctum. To access them:

1. Run the database seeder to generate a test user and token as described in the installation steps.

2. Include the token in your API requests using the Authorization header:
   ```
   Authorization: Bearer YOUR_TOKEN_HERE
   ```

3. If you need to generate a new token, you can use Laravel Tinker:
   ```bash
   php artisan tinker
   ```
   
   Then execute:
   ```php
   $user = \App\Models\User::where('email', 'some@email.com')->first();
   $user->tokens()->delete(); // Delete existing tokens
   $user->createToken('NewToken')->plainTextToken;
   ```

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
- `ExchangeRateApiTest`: Tests the API endpoints
- `FetchExchangeRatesCommandTest`: Tests the Artisan command
- `ExchangeRateServiceTest`: Tests the service layer

## Troubleshooting

- **API Authentication Issues**: Ensure Sanctum is properly configured
- **Exchange Rate Fetch Failures**: Check network connectivity to ECB
- **Database Connection Issues**: Verify database credentials in `.env`
- **API Documentation Issues**: If the documentation is not displaying correctly, try regenerating it with `php artisan scribe:generate`

