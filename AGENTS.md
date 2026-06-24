# AGENTS.md

## Cursor Cloud specific instructions

Open SEIMS is a single Laravel 12 + Filament 5 PHP application (not a monorepo). See `DOCUMENTATION.md` section 4 for the canonical setup guide.

### System dependencies (VM image, not update script)

The cloud VM needs:

- **PHP 8.3** with extensions: `mbstring`, `xml`, `curl`, `zip`, `sqlite3`, `mysql`, `gd`, `intl`, `bcmath`, `exif`
- **Composer** (installed to `~/.local/bin/composer`; add `export PATH="$HOME/.local/bin:$PATH"` to your shell)
- **Node.js 20.19+** (npm for Vite/Tailwind)
- **MariaDB/MySQL** for local dev seeding (see database note below)

### Update script vs manual setup

The startup update script only refreshes Composer/npm packages. One-time setup still requires:

1. `cp .env.example .env && php artisan key:generate`
2. Configure MySQL in `.env` (recommended for `db:seed`; SQLite seeding fails in `ServiceTypeSeeder` due to MySQL-only `IFNULL(created_at, NOW())` syntax)
3. Create DB/user, then `php artisan migrate --seed` and `php artisan storage:link`
4. `npm run build` (or run `npm run dev` alongside the app for HMR)

### Database (MariaDB)

Start MariaDB before running migrations or the app:

```bash
sudo mkdir -p /var/run/mysqld && sudo chown mysql:mysql /var/run/mysqld
sudo mysqld_safe --datadir=/var/lib/mysql &
```

Example `.env` database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=openseims
DB_USERNAME=openseims
DB_PASSWORD=openseims
```

### Running services

| Service | Command | Notes |
|---------|---------|-------|
| All dev services | `composer dev` | `artisan serve` + queue + pail + Vite (see `composer.json`) |
| Web only | `php artisan serve --host=0.0.0.0 --port=8000` | Filament SEIMS at `/`, admin at `/admin` |
| Frontend HMR | `npm run dev` | Only needed if not using `composer dev` |
| Queue worker | `php artisan queue:listen --tries=1` | Required for async CSV imports when `QUEUE_CONNECTION=database` |

Default seeded login: `national.admin@example.com` / `Pass1234`.

Local development with [Laravel Herd](https://herd.laravel.com) should use `http://seims.test` (`APP_URL` in `.env`). PHPUnit is configured the same way via `APP_URL=http://seims.test` in `phpunit.xml`.

### Lint and tests

- **Tests:** `composer test` (PHPUnit; uses in-memory SQLite, no external DB)
- **Lint:** `./vendor/bin/pint --test` (Laravel Pint)

Some Feature tests may fail on `import-failure-toast-poller` or permission matrix checks if the Livewire poller component is missing from the checkout.

### API smoke test

```bash
php artisan tinker --execute="echo App\Models\User::first()->createToken('demo')->plainTextToken;"
curl -H "Authorization: Bearer <token>" http://127.0.0.1:8000/api/user
```
