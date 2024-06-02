# Fund Transfer
Provides functionality to transfer funds between our customers accounts.

Uses PHP v8.3.
Laravel 11

## Installation
Run docker container:
```shell script
$ docker-compose up -d --build
```

Run composer install inside container:
```shell script
$ docker-compose exec php composer install
```

Copy .env.example to .env:
```shell script
$ docker-compose exec php cp .env.example .env
```

Run migrations:
```shell script
$ docker-compose exec php php artisan migrate
```

Seed database with 3 random users with random accounts:
```shell script
$ docker-compose exec php php artisan db:seed
```

Run tests to ensure everything is working:
```shell script
$ docker-compose exec php vendor/bin/phpunit 
```

Application will be available under ***localhost:8080***

## API

<localhost:8080/clients> (GET)

Lists all client id's.

---

<localhost:8080/clients/{client}/accounts> (GET)

Lists all accounts for given client id.

---

<localhost:8080/accounts/transfer> (POST)

Performs fund transfer between 2 accounts.

Required parameters:

1. amount (amount of currency to transfer)
2. currency (transaction currency)
3. source_account_id (transfer from)
4. target_account_id (transfer to)

---

<localhost:8080/accounts/{account}/transactions> (GET)

Lists all transactions for given account id.
