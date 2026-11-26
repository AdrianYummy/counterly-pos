# Verification record

Run date: 2026-11-26 (Asia/Manila)

| Check | Setup / expected result | Actual result | Status |
| --- | --- | --- | --- |
| Framework routes | `php artisan route:list`; management routes must use auth group | 33 expected application/framework routes listed; required resources present | Pass |
| Automated suite | In-memory SQLite isolation; no development/production records | 12 tests, 59 assertions | Pass |
| MariaDB install | Empty `counterly_pos` on MariaDB 10.4.32; migrate and seed | Four domain tables migrated; 10 products, 5 customers, 2 staff seeded | Pass |
| Logged-out access | GET and POST management requests must not mutate data | Feature test redirects both and confirms no inserted product | Pass |
| Login/logout | Correct username works; wrong password fails; logout clears auth | Automated feature test passed | Pass |
| Product validation/image | Invalid values fail; valid image is prepared and stored | Validation and fake 1600×900 upload test passed; WebP path/file asserted | Pass |
| Customer/staff rules | Optional fields, password preservation/replacement, hashing | Staff password tests and CRUD validation paths passed | Pass |
| Named sale/tampering | Stock 10, ₱25, qty 3; client seller/total ignored | Stored total ₱75, stock 7, authenticated seller | Pass |
| Insufficient sale | Qty 8 with stock 7 must leave both tables unchanged | No sale; stock remained 7 | Pass |
| Walk-in/exact stock | Null customer; selling all units reaches zero | Null persisted, history label shown, stock zero | Pass |
| Amount overflow | Total beyond decimal range must not write | Validation error; stock and sale count unchanged | Pass |
| Historical amount | Later product-price edit must not alter old sale | Stored total remained unchanged | Pass |
| Deletion/account safeguards | Referenced records and current account survive attempts | Automated checks passed | Pass |
| Stale product edit | Older stock form must not undo a deduction | Update rejected; newer stock/name preserved | Pass |
| Production assets | `npm run build`; pages load from manifest without dev server | Vite 6.4.6 built CSS/JS; HTTP page referenced hashed CSS | Pass |
| Platform requirements | `composer check-platform-reqs` | All required installed Composer platform items succeeded | Pass |
| Authenticated HTTP smoke | Real local server/login cookie; dashboard/products/sales return 200 | Seeded product and dashboard content rendered; no-store header present | Pass |
| CSRF over real HTTP | Authenticated POST from an untrusted origin with no token; no mutation | HTTP 419; product count stayed 10 | Pass |
| Concurrent InnoDB sale | Two separate PHP processes each request 4 units from MariaDB stock 5 | One success, one rejection, one sale row, final stock 1; fixture removed | Pass |
| Real browser visual/mobile | Desktop/mobile keyboard and upload flows in supported browsers | Responsive implementation exists; manual multi-browser inspection not executed | Pending |
| Hosted verification | Core flows on the actual HTTPS deployment | No hosting account/URL supplied | Pending |
| GitHub repository | Reviewed commit pushed to an assessor-accessible repo | No Git/GitHub credentials or destination supplied | Pending |

Ordinary Laravel feature tests disable CSRF middleware, so the CSRF result above was checked against the running local HTTP application with an authenticated session, an untrusted origin, and no token.
