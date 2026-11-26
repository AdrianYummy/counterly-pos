<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function record(int $productId, ?int $customerId, int $quantity, User $staff): Sale
    {
        return $this->recordMany([
            ['product_id' => $productId, 'quantity' => $quantity, 'error_key' => 'quantity'],
        ], $customerId, $staff)->first();
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int, error_key?: string}>  $items
     * @return Collection<int, Sale>
     */
    public function recordMany(array $items, ?int $customerId, User $staff): Collection
    {
        return DB::transaction(function () use ($items, $customerId, $staff) {
            $products = Product::query()
                ->whereIn('id', collect($items)->pluck('product_id')->unique())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $transactionId = (string) Str::uuid();
            $sales = collect();

            foreach ($items as $item) {
                /** @var Product|null $product */
                $product = $products->get($item['product_id']);
                $quantity = $item['quantity'];
                $errorKey = $item['error_key'] ?? 'quantity';

                if (! $product) {
                    throw ValidationException::withMessages([
                        $errorKey => 'This product is no longer available.',
                    ]);
                }

                if ($quantity > $product->stock_quantity) {
                    throw ValidationException::withMessages([
                        $errorKey => "Only {$product->stock_quantity} unit(s) of {$product->name} are available.",
                    ]);
                }

                $priceCents = $this->toCents((string) $product->price);
                if ($priceCents > intdiv(9_999_999_999, $quantity)) {
                    throw ValidationException::withMessages([
                        $errorKey => "The total for {$product->name} exceeds the supported amount.",
                    ]);
                }

                $totalCents = $priceCents * $quantity;
                $beforeStock = $product->stock_quantity;
                $product->decrement('stock_quantity', $quantity);
                $product->refresh();

                $sale = Sale::create([
                    'transaction_id' => $transactionId,
                    'product_id' => $product->id,
                    'customer_id' => $customerId,
                    'sold_by' => $staff->id,
                    'quantity' => $quantity,
                    'total_price' => sprintf('%d.%02d', intdiv($totalCents, 100), $totalCents % 100),
                ]);
                ActivityLog::recordStockSale($staff, $product, $sale, $beforeStock);
                $sales->push($sale);
            }

            return $sales;
        }, 3);
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }
}
