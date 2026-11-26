# Schema and business rules

## Relationships

- `products` has many `sales` through `sales.product_id`.
- `customers` has many `sales`; `sales.customer_id` is nullable for walk-ins.
- `users` has many `sales` through `sales.sold_by`.

All identifiers and foreign keys are signed auto-increment/integer columns. Money is stored as `DECIMAL(10,2)`. Every table has `created_at`; the models explicitly disable `updated_at` to match the supplied schema. Sales have an additional creation-date index for history ordering/filtering.

## Transaction rules

`SaleService` begins a database transaction and locks the selected product row. It rejects insufficient stock and totals above `99,999,999.99`, calculates with integer centavos, decrements stock, and inserts the sale. Any exception rolls both operations back. Submitted price, total, seller, and timestamp fields are ignored.

Sales are immutable in the UI. Restrictive foreign keys and controller checks block deletion of referenced products, customers, and staff, including a race where a sale appears after a deletion page was loaded. Unreferenced records may be deleted. Staff cannot delete their own session account or the final account.

Product edits submit the stock value originally loaded. The update locks/reloads the row and rejects a mismatched original value, preventing an older form from restoring inventory deducted by a concurrent sale.
