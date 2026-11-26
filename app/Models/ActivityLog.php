<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'actor_name',
        'subject_type',
        'subject_public_id',
        'subject_name',
        'action',
        'changes',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public static function recordEdit(
        User $actor,
        string $subjectType,
        string $subjectPublicId,
        string $subjectName,
        array $before,
        array $after,
        array $additionalChanges = [],
    ): ?self {
        return self::recordChange($actor, $subjectType, $subjectPublicId, $subjectName, 'updated', $before, $after, $additionalChanges);
    }

    public static function recordCreation(User $actor, string $subjectType, string $subjectPublicId, string $subjectName, array $values): self
    {
        return self::recordChange(
            $actor,
            $subjectType,
            $subjectPublicId,
            $subjectName,
            'created',
            array_fill_keys(array_keys($values), null),
            $values,
        );
    }

    public static function recordDeletion(User $actor, string $subjectType, string $subjectPublicId, string $subjectName): self
    {
        return self::create([
            'actor_id' => $actor->id,
            'actor_name' => $actor->full_name,
            'subject_type' => $subjectType,
            'subject_public_id' => $subjectPublicId,
            'subject_name' => $subjectName,
            'action' => 'deleted',
            'changes' => ['status' => ['from' => 'Active', 'to' => 'Deleted']],
        ]);
    }

    public static function recordStockSale(User $actor, Product $product, Sale $sale, int $beforeStock): self
    {
        return self::create([
            'actor_id' => $actor->id,
            'actor_name' => $actor->full_name,
            'subject_type' => 'product',
            'subject_public_id' => $product->public_id,
            'subject_name' => $product->name,
            'action' => 'sold',
            'changes' => [
                'stock_quantity' => ['from' => (string) $beforeStock, 'to' => (string) $product->stock_quantity],
                'quantity_sold' => ['from' => 'Not set', 'to' => (string) $sale->quantity],
                'transaction' => ['from' => 'Not set', 'to' => $sale->publicReference()],
            ],
        ]);
    }

    private static function recordChange(
        User $actor,
        string $subjectType,
        string $subjectPublicId,
        string $subjectName,
        string $action,
        array $before,
        array $after,
        array $additionalChanges = [],
    ): ?self {
        $changes = [];

        foreach ($after as $field => $newValue) {
            $oldValue = $before[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$field] = [
                    'from' => self::displayValue($oldValue),
                    'to' => self::displayValue($newValue),
                ];
            }
        }

        $changes = array_merge($changes, $additionalChanges);
        if ($changes === []) {
            return null;
        }

        return self::create([
            'actor_id' => $actor->id,
            'actor_name' => $actor->full_name,
            'subject_type' => $subjectType,
            'subject_public_id' => $subjectPublicId,
            'subject_name' => $subjectName,
            'action' => $action,
            'changes' => $changes,
        ]);
    }

    private static function displayValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'Not set';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return (string) $value;
    }
}
