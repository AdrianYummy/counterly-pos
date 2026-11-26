# Verification record

Run date: 2026-11-26 (Asia/Manila)

| Check | Setup / expected result | Actual result | Status |
| --- | --- | --- | --- |
| Framework routes | `php artisan route:list`; management routes must use auth group | 38 expected application/framework routes listed; required resources present | Pass |
| Automated suite | In-memory SQLite isolation; no development/production records | 40 tests, 278 assertions | Pass |
| MariaDB install | Existing `counterly_pos` on MariaDB 10.4.32; apply migrations | Users received recovery emails; password token table created; existing data preserved | Pass |
| Logged-out access | GET and POST management requests must not mutate data | Feature test redirects both and confirms no inserted product | Pass |
| Login/logout | Correct username works; wrong password fails; logout clears auth | Automated feature test passed | Pass |
| Session auto-logout | Expired activity, missing heartbeat, tab-close endpoint, and authenticated browser hooks | Server invalidated expired/closed sessions; heartbeat/activity refreshed live sessions; secure layout hooks rendered | Pass |
| Product validation/image | Invalid values fail; valid image is prepared and stored | Validation and fake 1600×900 upload test passed; WebP path/file asserted | Pass |
| Customer/staff rules | Optional fields, password preservation/replacement, hashing | Staff password tests and CRUD validation paths passed | Pass |
| Accountability audit | Create/update/delete products, customers, and staff; change product stock/price; sell stock; replace password | Actor, action, subject, old/new values, sale quantity/reference, and timestamps recorded; blocked actions absent; plaintext and password hash absent | Pass |
| List pagination | Create 12 products, customers, staff, transactions, and audit records | Page 1 returned 10 records with a page 2 button; page 2 returned the remaining 2 | Pass |
| Duplicate names | Submit product/staff/customer names and customer emails with different case/repeated spaces; attempt exact direct duplicates | Form requests rejected equivalent names/emails; unique database indexes rejected exact race-condition duplicates | Pass |
| Role enforcement | Cashier directly requests staff/product-write/customer-delete routes | Every prohibited request returned HTTP 403 with no mutation | Pass |
| Public identifiers | Generated product/customer/staff/sale URLs use UUIDs; numeric URLs attempted | UUID route segments matched; every numeric route returned 404 | Pass |
| Password storage | Create staff with a known password | Stored value differs from plaintext and passes one-way hash verification | Pass |
| Final administrator | Attempt to demote/delete the only administrator | Both blocked; administrator role preserved | Pass |
| Browser headers | Login response inspected | Clickjacking, MIME sniffing, referrer, opener, and permissions headers present | Pass |
| Password recovery | Request link, open reset form, submit token and strong password | Notification sent, hash replaced, token consumed; unknown email remained undisclosed | Pass |
| Customer receipt email | Named, walk-in, and resend paths with notification fake | Named customer notified; walk-in skipped; detail-page resend notified | Pass |
| UI production build | `npm run build` after green design/POS redesign | Vite built current CSS and JavaScript into `public/build` | Pass |
| Add Product route | Administrator requests `/products/create` | Static create route returned 200 instead of being captured as a product UUID | Pass |
| CaféFlow hero/branding | Public landing page, supplied logo, current production assets | Hero returned 200; logo, theme control, and current asset hashes rendered | Pass |
| Landing motion hooks | Features/workflow anchors, bean physics field, and sign-in transition markup | Required hooks rendered; Vite compiled animation and physics JavaScript successfully | Pass |
| Local reset preview | Log mailer with preview enabled | Valid request displayed a working development-only reset link | Pass |
| Real SMTP inbox delivery | Valid provider credentials and real staff/customer addresses | No external SMTP credentials supplied; log mailer remains the safe local default | Pending |
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
| Real browser visual/mobile | Desktop hero rendered in headless Edge; mobile/keyboard/upload flows in supported browsers | Desktop hero visually inspected; full manual multi-browser pass not executed | Pending |
| Hosted verification | Core flows on the actual HTTPS deployment | No hosting account/URL supplied | Pending |
| GitHub repository | Reviewed commit pushed to an assessor-accessible repo | No Git/GitHub credentials or destination supplied | Pending |

Ordinary Laravel feature tests disable CSRF middleware, so the CSRF result above was checked against the running local HTTP application with an authenticated session, an untrusted origin, and no token.
