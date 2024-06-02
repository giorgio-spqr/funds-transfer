<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 *
 * @property int|null $source_account_id
 * @property Account|null $sourceAccount
 *
 * @property int|null $target_account_id
 * @property-read Account|null $targetAccount
 *
 * @property float $rate
 * @property float $sent_amount
 * @property float $deducted_amount
 * @property string $currency
 *
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_account_id',
        'target_account_id',
        'rate',
        'sent_amount',
        'deducted_amount',
    ];

    public function sourceAccount(): ?BelongsTo
    {
        return $this->belongsTo(
            related: Account::class,
            foreignKey: 'source_account_id',
            ownerKey: 'id',
        );
    }

    public function targetAccount(): ?BelongsTo
    {
        return $this->belongsTo(
            related: Account::class,
            foreignKey: 'target_account_id',
            ownerKey: 'id',
        );
    }
}
