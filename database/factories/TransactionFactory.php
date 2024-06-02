<?php

namespace Database\Factories;

use App\Integration\Providers\CurrencyProvider;
use App\Models\Account;
use App\Models\Client;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [];
    }

    public function uniqueCurrency(array $currencies): Closure
    {
        return function (array $attributes) use ($currencies) {
            $clientId = $attributes['client_id'];

            $existingCurrencies = Account::query()
                ->where('client_id', $clientId)
                ->pluck('currency')
                ->toArray();

            $availableCurrencies = array_diff($currencies, $existingCurrencies);

            if (empty($availableCurrencies)) {
                throw new \Exception('No unique currencies available for the client.');
            }

            return $availableCurrencies[array_rand($availableCurrencies)];
        };
    }
}
