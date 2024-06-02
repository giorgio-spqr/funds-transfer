<?php

namespace App\Http\Resources;

use App\Models\Account;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray($request): ?array
    {
        $resource = $this->resource;
        if (!$resource instanceof Account) {
            return null;
        }

        return [
            'id' => $resource->id,
            'client_id' => $resource->client_id,
            'currency' => $resource->currency,
            'balance' => (float) $resource->balance,
        ];
    }
}
