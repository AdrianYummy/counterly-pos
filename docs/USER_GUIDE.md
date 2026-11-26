# User guide

1. Sign in with a staff username and password. Cashiers open directly on **Point of Sale**; administrators open on **Dashboard**, where accountability records show who created, updated, sold, or deleted products, stock, customers, and staff accounts, including the changed fields and time. There is no public registration.
2. Use **Products** to search and view inventory. Administrators can add, edit, or delete unused products; cashiers cannot change inventory records directly. Zero and low stock are labeled automatically.
3. Use **Customers** for contact records. A sale may instead remain a walk-in transaction.
4. Administrators can use **Staff Accounts** to create login accounts, assign Administrator or Cashier roles, replace passwords, and manage optional avatars. Leaving password blank during edit preserves it. Cashiers cannot open or call staff-management routes.
5. Open **Point of Sale**, search and select any in-stock products, then adjust each quantity in the cart. Select a chosen product again or use its × button to remove a mistake. Optionally select a customer and choose **Complete Sale**. Enable **Email receipt to customer** when a named customer is selected. The server recalculates every line and cancels the entire checkout if any item lacks stock.
6. Use **Transactions** to search/filter immutable sales and open receipt details. A named-customer receipt can be emailed again or printed.
7. Use **Sign out** in the header when a shift ends. CaféFlow confirms the action before closing the staff session. Refreshing an authenticated page keeps the user signed in, while closing and later restoring the tab requires a fresh sign-in. An inactive workspace signs out after 15 minutes, and the server rejects a session whose browser heartbeat has stopped after a crash.

Long product, customer, staff, transaction, and accountability lists show 10 records at a time. Use the numbered Previous/Next controls below each list to open the remaining pages without loading every record at once.

Product names and staff full names cannot be duplicated. Capitalization and repeated spaces do not create a different name; choose a genuinely distinct name when the form reports that one already exists.

Customer full names and email addresses must also be unique. Changing capitalization or adding spaces does not bypass the duplicate-customer check.

If a deletion is blocked, the record participates in sales history and must be preserved. If a product stock edit reports stale data, reload the edit page and apply the intended value to the newest stock count.

For a forgotten password, choose **Forgot password?** on the login screen and enter the email assigned to the staff account. The link expires after 60 minutes and can be used only once. For local testing with `MAIL_MAILER=log`, read the generated message in `storage/logs/laravel.log`; configure SMTP for real inbox delivery.

Use the moon/sun control to switch between light and dark themes. CaféFlow remembers the choice in the browser. On desktop, the panel control beside the page title minimizes or restores the navigation sidebar; hover or focus a collapsed icon to see its section label. The CaféFlow logo always returns to the appropriate landing or workspace page.

On the public landing page, **Features** and **How it works** scroll smoothly to their sections. Decorative coffee beans react to mouse movement without capturing clicks, and staff sign-in links use a short bean-crossing transition. These animations are disabled automatically when the browser requests reduced motion.

Application URLs use random UUID references rather than internal database numbers. These references should still be treated as application data; role checks, authentication, and record validation provide the actual access control.
