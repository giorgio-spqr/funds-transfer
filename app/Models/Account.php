<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property-read int $id
 *
 * @property int $client_id
 * @property-read Client $client
 *
 * @property float $balance
 * @property string $currency
 *
 * @property-read Transaction[]|Collection $sentTransactions
 * @property-read Transaction[]|Collection $receivedTransactions
 *
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'balance',
        'currency',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(
            related: Client::class,
            foreignKey: 'id',
            ownerKey: 'client_id',
        );
    }

    public static function getById(mixed $id): ?Account
    {
        if (!is_int($id)) {
            return null;
        }

        return self::query()->find($id);
    }

    public function sentTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'source_account_id')
            ->orderBy('created_at' , 'desc');
    }

    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'target_account_id')
            ->orderBy('created_at', 'desc');
    }
}
