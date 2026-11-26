<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasPublicId;

    public const UPDATED_AT = null;

    protected $fillable = ['name', 'price', 'stock_quantity', 'image'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'stock_quantity' => 'integer', 'created_at' => 'datetime'];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
