# Schema and business rules

## Relationships

- `products` has many `sales` through `sales.product_id`.
- `customers` has many `sales`; `sales.customer_id` is nullable for walk-ins.
- `users` has many `sales` through `sales.sold_by`.
- `password_reset_tokens` stores a hashed, expiring token keyed by a staff email and is deleted after a successful reset.
- `activity_logs` stores the acting staff member, action, product/customer/staff public reference, timestamp, and field-level before/after values for successful creates, updates, deletions, and sale-driven stock deductions. Product entries include price, image, stock quantity, quantity sold, and transaction reference changes. Password values and hashes are never recorded; a password replacement is stored only as “Updated securely.”

Primary identifiers and foreign keys remain signed auto-increment/integer columns internally. Users, products, customers, and sales also have unique UUID `public_id` values used for route binding, filter values, and sale-form record references. Internal numeric IDs are therefore not disclosed through application URLs. Money is stored as `DECIMAL(10,2)`. Every table has `created_at`; the models explicitly disable `updated_at` to match the supplied schema. Sales have an additional creation-date index for history ordering/filtering.

Staff email addresses are unique and required by application validation. They are used for account recovery. Customer email addresses can receive an optional transaction receipt; sending occurs only after the stock/sale transaction has committed, so a mail-server failure cannot undo or duplicate a completed sale.

Product names and staff full names have unique database indexes. Form validation compares names without case and collapses repeated spaces, so names such as `House Blend`, `house blend`, and `House   Blend` are treated as the same record name. Editing a record without changing its own name remains valid.

Customer full names and email addresses are also unique. Customer names use the same case-insensitive, repeated-space-normalized comparison, while email addresses are normalized to lowercase. Existing customers may save their own unchanged name and email.

## Roles and authorization

- Administrators can manage staff accounts, create/edit/delete products, and delete unreferenced customers.
- Cashiers can view products, manage customer contact details, record sales, and view sales history.
- Both roles must be authenticated. Server-side `admin` middleware returns HTTP 403 for prohibited direct requests; menu visibility is only a usability aid.
- The final administrator cannot be demoted or deleted, and users cannot delete their own active account.

## Transaction rules

`SaleService` begins a database transaction and locks the selected product row. It rejects insufficient stock and totals above `99,999,999.99`, calculates with integer centavos, decrements stock, and inserts the sale. Any exception rolls both operations back. Submitted price, total, seller, and timestamp fields are ignored.

Sales are immutable in the UI. Restrictive foreign keys and controller checks block deletion of referenced products, customers, and staff, including a race where a sale appears after a deletion page was loaded. Unreferenced records may be deleted by an administrator. Staff cannot delete their own session account, the final account, or the final administrator.

Product edits submit the stock value originally loaded. The update locks/reloads the row and rejects a mismatched original value, preventing an older form from restoring inventory deducted by a concurrent sale.
