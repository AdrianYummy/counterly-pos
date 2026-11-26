# User guide

1. Sign in with a staff username and password. Cashiers open directly on **Point of Sale**; administrators open on **Dashboard**. There is no public registration.
2. Use **Products** to search and view inventory. Administrators can add, edit, or delete unused products; cashiers cannot change inventory records directly. Zero and low stock are labeled automatically.
3. Use **Customers** for contact records. A sale may instead remain a walk-in transaction.
4. Administrators can use **Staff Accounts** to create login accounts, assign Administrator or Cashier roles, replace passwords, and manage optional avatars. Leaving password blank during edit preserves it. Cashiers cannot open or call staff-management routes.
5. Open **Point of Sale**, search and select an in-stock product, optionally select a customer, enter a whole-number quantity, and choose **Complete Sale**. Enable **Email receipt to customer** when a named customer is selected. The displayed preview is informational; the server recalculates and checks everything.
6. Use **Transactions** to search/filter immutable sales and open receipt details. A named-customer receipt can be emailed again or printed.
7. Use **Sign out** in the header when a shift ends. CaféFlow confirms the action before closing the staff session.

If a deletion is blocked, the record participates in sales history and must be preserved. If a product stock edit reports stale data, reload the edit page and apply the intended value to the newest stock count.

For a forgotten password, choose **Forgot password?** on the login screen and enter the email assigned to the staff account. The link expires after 60 minutes and can be used only once. For local testing with `MAIL_MAILER=log`, read the generated message in `storage/logs/laravel.log`; configure SMTP for real inbox delivery.

Use the moon/sun control to switch between light and dark themes. CaféFlow remembers the choice in the browser. On desktop, the panel control beside the page title minimizes or restores the navigation sidebar. The CaféFlow logo always returns to the appropriate landing or workspace page.

Application URLs use random UUID references rather than internal database numbers. These references should still be treated as application data; role checks, authentication, and record validation provide the actual access control.
