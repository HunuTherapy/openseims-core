# AGENTS.md

## Cursor Cloud specific instructions

Open SEIMS is a **Laravel 12 + Filament 5** PHP application for special education data management.

### System prerequisites (VM image)

These are installed once on the Cloud VM (not in the update script):

- PHP 8.3+ with extensions: `mbstring`, `intl`, `gd`, `exif`, `sqlite3`, `mysql`, `xml`, `curl`, `zip`, `bcmath`
- Composer 2.x at `~/.local/bin/composer` (ensure `PATH` includes `$HOME/.local/bin`)
- Node.js 20.19+ and npm
- MariaDB server

### Database

- **Use MySQL/MariaDB for full dev** — seeders use MySQL-specific SQL (e.g. `IFNULL(created_at, NOW())` in `ServiceTypeSeeder`). SQLite from `.env.example` will fail during `db:seed`.
- Start MariaDB before running the app: `sudo service mariadb start`
- Example local DB: database `openseims`, user/password `openseims` (create via `sudo mysql` if needed)

### First-time setup (after clone)

See `DOCUMENTATION.md` §4. Summary:

```bash
cp .env.example .env
# Configure MySQL in .env (see above), then:
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm run build
```

### Running services

| Service | Command |
|---------|---------|
| Web server | `php artisan serve --host=0.0.0.0 --port=8000` |
| Queue worker | `php artisan queue:listen --tries=1` (required for CSV imports/exports) |
| All-in-one dev | `composer dev` (serve + queue + pail + Vite) |

Panels: **`/`** (SEIMS operational panel), **`/admin`** (national admin only).

### Seeded login credentials

| Role | Email | Password |
|------|-------|----------|
| National Admin | `national.admin@example.com` | `Pass1234` |
| District Officer | `district.officer@example.com` | `Pass1234` |
| School Coordinator | `school.coordinator@example.com` | `Pass1234` |

### Lint and test

- **Lint:** `./vendor/bin/pint --test`
- **Tests:** `composer test` (PHPUnit; uses in-memory SQLite, no running services)

### Known gotchas

- `App\Livewire\ImportFailureToastPoller` must exist — `AppServiceProvider` registers a render hook that references it; without the class, Filament pages return HTTP 500.
- Four PHPUnit tests may fail on `main` related to the missing poller component and permission UI (117/121 pass as of initial setup).
- Health check: `GET /up` returns 200 when the app is running.
