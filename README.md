# Architecture

A task was created to store the latest price of Bitcoin, DACXI, ETH, ATOM, LUNA in USD, AUD and BRL in the database using CoinGecko's "simple/price" API. This task runs every minute, thus creating a price history.

CoinGecko's "coins/**id**/market_chart/range" API was used to retrieve prices if the information is not found in the database (using a window of +/- 3 hours from the requested date/time). This information is saved in the database to prevent future calls to the CoinGecko API.



# API

### Recent coin price

URL: http://127.0.0.1:8000/api/latest-price

Query parameters:

- currency - possible values are "usd" (default value), "aud" and "brl"
- coin - possible values are "btc" (default value), "dacxi", "eth", "atom" and "luna"

### Estimated price at datetime

URL: http://127.0.0.1:8000/api/history-price

Query parameters:

- currency - possible values are "usd" (default value), "aud" and "brl"
- coin - possible values are "btc" (default value), "dacxi", "eth", "atom" and "luna"
- date - required parameter formatted as "Y-m-d"
- time - time formatted as "H:i:s" (default value 00:00:00)



# Local environment setups

### Requirements

- PHP 8.1 with extensions BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, SQLite3 , PDO SQLite3 and Curl;
- Composer 2.

### Installation

Execute the follow commands inside the working directory:

```bash
cp .env.example .env
touch database\database.sqlite
composer install
php artisan key:generate
php artisan migrate
php artisan crypto:update
```

### Running the application

Execute the follow commands in different shells (tabs) inside the working directory:

```bash
php artisan serve
php artisan schedule:work
```

