<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property-read int $id
 * @property-read Account[]|Collection $accounts
 *
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Client extends Model
{
    use HasFactory;

    public function accounts(): HasMany
    {
        return $this->hasMany(
            related: Account::class,
            foreignKey: 'client_id',
        );
    }

    public static function getById(mixed $id): ?Client
    {
        if (!is_int($id)) {
            return null;
        }

        return self::query()->find($id);
    }
}
