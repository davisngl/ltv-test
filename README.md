# Introduction
TV Guide API for getting daily broadcast, current broadcast on-air and what's coming next in context of airings.

## Setup
Project uses the minimum version of **PHP 8.3**.
It is optimized for usage with **Laravel Sail** with all the needed services.

1. Clone the repository locally and go to the root of that directory
2. `cp .env.example .env`
3. Turn off locally running MySQL, Redis and Nginx (if applicable) as port interfering will happen when running Sail
4. `./vendor/bin/sail up -d` - this will take some time while images are pulled down and services are spun up
5. `./vendor/bin/sail artisan migrate --seed` to run your migrations and to seed the initial data

**NB:** This will provide you with initial 3 initial channels and Sanctum API token in the console output.

## API
API is protected using Sanctum API tokens (should be provided as `Bearer` tokens in your chosen API client).  
**Postman**-compatible API request collection was provided beforehand.

### Rate limiting
Max 20 requests / minute, limited by used IP address and API token.
If needed, request amount is configurable in `config/api.php`.

### Additional / new API tokens
You can run `./vendor/bin/sail tinker`. Once it opens, run `User::first()->createToken('call it whatever');` and it will output `plainTextToken` - that you can use for your `Bearer` token from now on.  
Tokens have no expiration.

## Testing
You can run the test suite using `./vendor/bin/sail test`.

## Quality tooling
In `composer.json` you will have 2 scripts - `fix-styles` (fix code style according to LaraveL Pint) and `ide-helper` (generate model metadata for better IDE autocomplete support).
You can run those scripts like so `./vendor/bin/sail composer run <script>` or just `./vendor/bin/sail composer <script>`.

# What could've been done but out of time
1. Static analysis
2. A lot more of tests. Mostly tested the happy paths, but there can be a lot more validation bugs, edge cases, datetime boundary and mutability bugs etc. This is mostly shown that I know testing.
3. DTOs could've been bound to an interface for easier runtime swaps for testing or normal use, but not sure if I should put everything under the Sun on interface in order to be able to swap it out.
