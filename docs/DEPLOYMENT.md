# Deployment and submission

Use a PHP host with PHP 8.2+, the Laravel extensions listed in the README, MySQL/MariaDB, HTTPS, persistent writable storage, and a document root pointed at `public/`. Static GitHub Pages cannot run this application.

## Production checklist

1. Push the reviewed source and lockfiles to a private/public repository appropriate for the class submission.
2. Set production environment values outside source control: `APP_ENV=production`, `APP_DEBUG=false`, the HTTPS `APP_URL`, a stable generated `APP_KEY`, database credentials, `SESSION_SECURE_COOKIE=true`, `SESSION_EXPIRE_ON_CLOSE=true`, `SESSION_IDLE_TIMEOUT=900`, `SESSION_HEARTBEAT_TIMEOUT=180`, `SESSION_CLOSE_GRACE=10`, file sessions/cache, synchronous queue, SMTP credentials/from address, `MAIL_PREVIEW_LINK=false`, and a private initial-account password if intentionally seeding a new installation.
3. Install/build the exact reviewed version:

   ```sh
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   php artisan migrate --force
   php artisan optimize
   ```

   If the host has no Node.js, upload the committed `public/build` from the reviewed commit.

4. Give the web process write access to `storage` and `bootstrap/cache`. Keep `.env`, source, vendor metadata, and storage internals outside public access. Persist `storage/app/public` and `storage/framework/sessions` across deployments. CaféFlow serves authenticated product images and staff avatars through its `/media` route, so hosts without SSH or symbolic-link support do not require `php artisan storage:link`.
5. Seed only a new demo environment deliberately. Never run `migrate:fresh`, destructive resets, or automatic demo seeding against existing/production data.
6. Verify on the real HTTPS URL: login/logout, direct-route protection, all CRUD, image upload/display after redeploy, named/walk-in sale, insufficient stock, history, secure cookies, and production assets without a Vite dev server.
7. Put the real repository URL, live URL, and deployed commit in the README and testing record. Supply passwords privately.

## GitHub preparation

The project `.gitignore` excludes `.env`, `vendor`, `node_modules`, logs, sessions, caches, and generated uploads. It intentionally keeps dependency manifests/lockfiles, migrations, seeders, source assets, and `public/build`. Review with `git status`, commit, create the repository through your authorized GitHub account, add its real remote, and push. Do not invent a repository or deployment URL.
