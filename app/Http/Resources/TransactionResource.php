<?php

namespace App\Http\Resources;

use App\Enum\TransactionType;
use App\Models\Transaction;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request): ?array
    {
        $resource = $this->resource;
        if (!$resource instanceof Transaction) {
            return null;
        }

        $type = TransactionType::Unknown;
        $clientId = $request->account?->client_id;

        if ($clientId) {
            $isSender = $resource->sourceAccount->client_id == $clientId;
            $type = $isSender ? TransactionType::Outcome : TransactionType::Income;
        }

        return [
            'id' => $resource->id,
            'source_account_id' => $resource->source_account_id,
            'target_account_id' => $resource->target_account_id,
            'type' => $type,
            'rate' => (float) $resource->rate,
            'sent_amount' => (float) $resource->sent_amount,
            'deducted_amount' => (float) $resource->deducted_amount,
            'currency' => $resource->currency,
            'created_at' => $resource->created_at->toDateTimeString(),
        ];
    }
}
