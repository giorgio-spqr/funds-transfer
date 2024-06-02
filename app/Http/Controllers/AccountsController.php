<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Http\Resources\AccountResource;
use App\Http\Resources\TransactionResource;
use App\Integration\Providers\CurrencyProvider;
use App\Models\Account;
use App\Models\Client;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountsController extends Controller
{
    public function index(Request $request, Client $client): JsonResponse
    {
        $resources = AccountResource::collection($client->accounts);
        $resources::withoutWrapping();

        return $resources->toResponse($request);
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        $currency = $request->input('currency');

        $sourceAccountId = $request->input('source_account_id');
        $targetAccountId = $request->input('target_account_id');

        $amount = $request->input('amount');

        $sourceAccount = Account::getById($sourceAccountId);
        $targetAccount = Account::getById($targetAccountId);

        $transaction = $this->makeTransaction($sourceAccount, $targetAccount, $amount, $currency);
        if (!$transaction instanceof Transaction) {
            return response()->json([
                'result' => 'failed',
            ]);
        }

        return response()->json([
            'result' => 'success',
        ]);
    }

    public function transactions(Request $request, Account $account): JsonResponse
    {
        $request->validate([
            'limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'offset' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $limit = (int) $request->query('limit', 100);
        $offset = (int) $request->query('offset', 0);

        $sentTransactionsQuery = $account->sentTransactions;
        $receivedTransactionsQuery = $account->receivedTransactions;

        $merged = $sentTransactionsQuery->merge($receivedTransactionsQuery);

        $count = $merged->count();

        $transactions = $merged
            ->sortBy(callback: 'created_at', descending: true)
            ->slice($offset, $limit);

        $resources = TransactionResource::collection($transactions);

        $resources->additional([
            'meta' => [
                'total' => $count,
                'limit' => $limit,
                'offset' => $offset,
            ],
        ]);

        return $resources->toResponse($request);
    }

    private function makeTransaction(
        Account $sourceAccount,
        Account $targetAccount,
        float $amount,
        string $currency,
    ): ?Transaction
    {
        $rate = 1;

        if ($currency != $sourceAccount->currency) {
            /** @var CurrencyProvider $provider */
            $provider = app(CurrencyProvider::class);

            $rate = $provider->getExchangeRateForCurrencies($currency, $sourceAccount->currency);

            if (!$rate) {
                return null;
            }
        }

        $deductedAmount = $amount * $rate;

        if ($deductedAmount > $sourceAccount->balance) {
            return null;
        }

        DB::beginTransaction();

        try {
            $sourceAccount->balance = $sourceAccount->balance - $deductedAmount;
            $targetAccount->balance = $targetAccount->balance + $amount;

            $transaction = new Transaction();

            $transaction->source_account_id = $sourceAccount->id;
            $transaction->target_account_id = $targetAccount->id;
            $transaction->rate = $rate;
            $transaction->sent_amount = $amount;
            $transaction->deducted_amount = $deductedAmount;
            $transaction->currency = $currency;

            $transaction->save();
            $sourceAccount->save();
            $targetAccount->save();

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();

            $transaction = null;
        }

        return $transaction;
    }
}
