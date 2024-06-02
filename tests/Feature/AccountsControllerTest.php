<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Client;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AccountsControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_index(): void
    {
        $client = Client::factory()
            ->has(Account::factory()->count(3), 'accounts')
            ->create();

        $response = $this->getJson(route('client.accounts', ['client' => $client->id]));
        $response->assertStatus(200);

        $response->assertJsonCount(3);

        $response->assertJsonStructure([
            '*' => [
                'id',
                'client_id',
                'currency',
                'balance',
            ]
        ]);
    }

    public function test_transfer(): void
    {
        $sourceAccount = $this->createAccount('EUR', 10000);
        $targetAccount = $this->createAccount('USD', 10000);

        $oldSourceBalance = $sourceAccount->balance;
        $oldTargetBalance = $targetAccount->balance;

        // trying correct currency

        $response = $this->makeTransaction($sourceAccount, $targetAccount, 100, 'USD');

        $sourceAccount->refresh();
        $targetAccount->refresh();

        $response->assertStatus(200)
            ->assertJson(['result' => 'success']);

        $this->assertLessThan($oldSourceBalance, $sourceAccount->balance);
        $this->assertGreaterThan($oldTargetBalance, $targetAccount->balance);

        $transactions = Transaction::query()->get();

        $this->assertEquals(1, $transactions->count());

        // trying wrong currency

        $response = $this->makeTransaction($sourceAccount, $targetAccount, 100, 'JPY');

        $sourceAccount->refresh();
        $targetAccount->refresh();

        $response->assertStatus(422);
    }

    public function test_transactions(): void
    {
        $firstAccount = $this->createAccount('USD', 10000);
        $secondAccount = $this->createAccount('EUR', 10000);

        $accountIds = [$firstAccount->id, $secondAccount->id];

        Transaction::factory()
            ->count(10)
            ->create([
                'source_account_id' => function() use ($accountIds) {
                    return Arr::random($accountIds);
                },
                'target_account_id' => function(array $attributes) use ($accountIds) {
                    $targetAccountIds = array_diff($accountIds, [$attributes['source_account_id']]);
                    return Arr::random($targetAccountIds);
                },
                'rate' => $this->faker->randomFloat(4, 0.0001, 25),
                'sent_amount' => $this->faker->randomFloat(6, 1, 25000),
                'deducted_amount' => $this->faker->randomFloat(6, 1, 25000),
                'currency' => 'EUR',
        ]);

        $response = $this->getJson(route('account.transactions', ['account' => $firstAccount->id]));

        $response->assertStatus(200);

        $response->assertJsonCount(10, 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'source_account_id',
                    'target_account_id',
                    'rate',
                    'sent_amount',
                    'deducted_amount',
                    'currency',
                    'created_at',
                ],
            ],
            'meta' => [
                'total',
                'limit',
                'offset',
            ],
        ]);
    }

    private function makeTransaction(
        Account $sourceAccount,
        Account $targetAccount,
        float $amount,
        string $currency,
    ): TestResponse
    {
        return $this->postJson(route('accounts.transfer'), [
            'currency' => $currency,
            'source_account_id' => $sourceAccount->id,
            'target_account_id' => $targetAccount->id,
            'amount' => $amount,
        ]);
    }

    private function createAccount(string $currency, float $balance): Account
    {
        $client = Client::factory()->create();
        return Account::factory()->create([
            'client_id' => $client->id,
            'currency' => $currency,
            'balance' => $balance,
        ]);
    }
}
