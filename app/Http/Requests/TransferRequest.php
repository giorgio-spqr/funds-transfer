<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'source_account_id' => [
                'required',
                Rule::exists(Account::class, 'id'),
            ],
            'target_account_id' => [
                'required',
                'different:source_account_id',
                Rule::exists(Account::class, 'id'),
            ],
            'currency' => [
                'required',
                function ($attribute, $value, $fail) {
                    $targetAccountId = $this->input('target_account_id');

                    $targetAccount = Account::getById($targetAccountId);

                    $isSameCurrency = $value == $targetAccount?->currency;

                    if (!$isSameCurrency) {
                        return $fail('Target account has different currency');
                    }

                    return true;
                },
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.000001',
                'max:10000000000'
            ],
        ];
    }
}
