<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function record(int $productId, ?int $customerId, int $quantity, User $staff): Sale
    {
        return DB::transaction(function () use ($productId, $customerId, $quantity, $staff) {
            /** @var Product $product */
            $product = Product::query()->lockForUpdate()->findOrFail($productId);
            if ($quantity > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$product->stock_quantity} unit(s) are available.",
                ]);
            }

            $priceCents = $this->toCents((string) $product->price);
            if ($priceCents > intdiv(9_999_999_999, $quantity)) {
                throw ValidationException::withMessages([
                    'quantity' => 'This sale total exceeds the supported amount.',
                ]);
            }

            $totalCents = $priceCents * $quantity;
            $beforeStock = $product->stock_quantity;
            $product->decrement('stock_quantity', $quantity);
            $product->refresh();

            $sale = Sale::create([
                'product_id' => $product->id,
                'customer_id' => $customerId,
                'sold_by' => $staff->id,
                'quantity' => $quantity,
                'total_price' => sprintf('%d.%02d', intdiv($totalCents, 100), $totalCents % 100),
            ]);
            ActivityLog::recordStockSale($staff, $product, $sale, $beforeStock);

            return $sale;
        }, 3);
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }
}
