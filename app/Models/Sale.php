<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    use HasFactory, HasPublicId;

    public const UPDATED_AT = null;

    protected $fillable = ['transaction_id', 'product_id', 'customer_id', 'sold_by', 'quantity', 'total_price'];

    protected function casts(): array
    {
        return ['quantity' => 'integer', 'total_price' => 'decimal:2', 'created_at' => 'datetime'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    public function transactionReference(): string
    {
        $reference = $this->transaction_id ?: $this->public_id;

        return strtoupper(substr(str_replace('-', '', (string) $reference), 0, 8));
    }

    public function receiptItems(): Collection
    {
        return static::query()
            ->with(['product', 'customer', 'staff'])
            ->when(
                $this->transaction_id,
                fn ($query, $transactionId) => $query->where('transaction_id', $transactionId),
                fn ($query) => $query->whereKey($this->getKey()),
            )
            ->orderBy('id')
            ->get();
    }
}
