# MySQL Migration Rehearsal

Portfolio V2 ships seven migrations against a production MySQL database. The automated test
suite runs on **in-memory SQLite**, which proves the migrations work on one engine but says
nothing about MySQL.

**SQLite is not MySQL.** The most important migration,
`2026_10_04_100500_relax_legacy_not_null_columns`, uses genuinely different SQL per driver:

| Driver | Mechanism |
|--------|-----------|
| MySQL / MariaDB | `ALTER TABLE ... MODIFY \`col\` VARCHAR(255) NULL` — in-place, metadata-only |
| PostgreSQL | `ALTER COLUMN ... DROP NOT NULL` |
| SQLite | Table rebuild driven by `PRAGMA table_info` (test-only path) |

`100400_backfill_portfolio_slugs_and_names` also runs real `UPDATE` statements against real
rows, so it is the migration most likely to surface surprises.

**A rehearsal is therefore mandatory before deploying.** This document is the procedure.

> **Never run any of this against the production database.**
> Every command below targets a disposable copy. Read the command before running it.

---

## Why a rehearsal is required

The rehearsal answers four questions SQLite cannot:

1. Does `ALTER TABLE ... MODIFY` succeed on your MySQL version and storage engine?
2. Does the `projects.slug` unique index build correctly when the table already has rows?
3. Does the backfill produce correct, unique slugs on **your actual data**?
4. Does every existing row survive with `link`, `image` and `title` intact?

---

## Prerequisites

- Access to a MySQL/MariaDB server that is **not** production
- `mysqldump` and `mysql` client tools
- The same MySQL major version as production
- Free disk space for roughly 2× the current `srj_portfolio` dump

Check your production version first:

```sql
SELECT VERSION();
```

---

## 1. Back up production

```bash
mysqldump --single-transaction --routines --triggers \
  -h PRODUCTION_HOST -u BACKUP_USER -p \
  srj_portfolio > ~/backups/srj_portfolio_$(date +%Y%m%d_%H%M%S).sql
```

`--single-transaction` gives a consistent snapshot without locking tables.

**Verify the dump before continuing.** An empty or truncated file is worse than none:

```bash
ls -lh ~/backups/srj_portfolio_*.sql
grep -c "INSERT INTO" ~/backups/srj_portfolio_*.sql
head -5 ~/backups/srj_portfolio_*.sql
```

Record the row counts now so you can prove nothing was lost:

```sql
SELECT 'projects', COUNT(*) FROM projects
UNION ALL SELECT 'skills', COUNT(*) FROM skills
UNION ALL SELECT 'certificates', COUNT(*) FROM certificates
UNION ALL SELECT 'users', COUNT(*) FROM users;
```

Save this output. You will compare against it in step 6.

---

## 2. Create the rehearsal database

```bash
mysql -h REHEARSAL_HOST -u ADMIN_USER -p \
  -e "CREATE DATABASE srj_portfolio_rehearsal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Use a name that cannot be confused with production. `srj_portfolio_rehearsal` is deliberate.

---

## 3. Restore into the rehearsal database

```bash
mysql -h REHEARSAL_HOST -u ADMIN_USER -p \
  srj_portfolio_rehearsal < ~/backups/srj_portfolio_latest.sql
```

Confirm the data arrived:

```bash
mysql -h REHEARSAL_HOST -u ADMIN_USER -p srj_portfolio_rehearsal \
  -e "SELECT COUNT(*) AS projects FROM projects;
      SELECT COUNT(*) AS skills FROM skills;
      SELECT COUNT(*) AS certificates FROM certificates;
      SELECT COUNT(*) AS users FROM users;"
```

These numbers must match step 1.

---

## 4. Point a temporary environment at the rehearsal database

```bash
cp .env .env.rehearsal
```

Edit **`.env.rehearsal`** only:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=REHEARSAL_HOST
DB_PORT=3306
DB_DATABASE=srj_portfolio_rehearsal
DB_USERNAME=REHEARSAL_USER
DB_PASSWORD=REHEARSAL_PASSWORD
```

> Confirm `DB_DATABASE` says `srj_portfolio_rehearsal` and **not** `srj_portfolio`.
> This single check is what prevents a catastrophic mistake.

`.env.rehearsal` is not read by Laravel automatically. Either export the variables for the
session, or use `--env`:

```bash
# Option A: export for this shell only
set -a && . ./.env.rehearsal && set +a

# Option B: Laravel's --env flag
php artisan migrate --env=rehearsal
```

Double-check which database is active before every command:

```bash
php artisan tinker --execute="echo DB::connection()->getDatabaseName();"
```

It must print `srj_portfolio_rehearsal`.

---

## 5. Run the migrations

```bash
php artisan migrate:status
php artisan migrate --force
php artisan migrate:status
```

Expected: the seven `2026_10_04_*` migrations report `Ran`. Anything else — especially a
`100500` failure — means **stop** and restore; do not attempt a manual fix on the rehearsal
database.

If `100500` fails, capture the exact MySQL error before retrying. Common causes:

| Error | Cause |
|-------|-------|
| `Unknown column 'link' in 'projects'` | The table predates an earlier migration; re-check `migrate:status` |
| `BLOB/TEXT column 'link' can't have a default value` | Column type differs from expectation on this MySQL version |
| `Duplicate entry` on `projects.slug` | Pre-existing duplicate slugs; inspect before retrying |

---

## 6. Verify the data survived

```sql
-- Row counts must be identical to step 1.
SELECT 'projects', COUNT(*) FROM projects
UNION ALL SELECT 'skills', COUNT(*) FROM skills
UNION ALL SELECT 'certificates', COUNT(*) FROM certificates
UNION ALL SELECT 'users', COUNT(*) FROM users;

-- Every legacy URL must still exist.
SELECT COUNT(*) AS projects_with_link FROM projects WHERE link IS NOT NULL AND link <> '';
SELECT COUNT(*) AS certificates_with_link FROM certificates WHERE link IS NOT NULL AND link <> '';
SELECT COUNT(*) AS skills_with_image FROM skills WHERE image IS NOT NULL AND image <> '';

-- Every project must now have a slug, and they must be unique.
SELECT COUNT(*) AS total, COUNT(DISTINCT slug) AS distinct_slugs,
       SUM(slug IS NULL OR slug = '') AS missing
FROM projects;

-- live_url must be populated wherever link held a real URL.
SELECT COUNT(*) AS live_url_copied FROM projects
WHERE live_url IS NOT NULL AND live_url <> '';

-- The three relaxed columns must now accept NULL.
SELECT COUNT(*) AS null_links FROM projects WHERE link IS NULL;

-- Every existing user must still be an administrator.
SELECT COUNT(*) AS total_users, SUM(is_admin = 1) AS admins FROM users;
```

All of these should hold:

- Row counts identical to step 1
- `total = distinct_slugs` and `missing = 0`
- `live_url_copied >= projects_with_link_that_were_urls`
- `admins = total_users` (no administrator locked out)

---

## 7. Run the application against the rehearsal database

```bash
php artisan optimize:clear
npm ci && npm run build

php artisan test
php artisan serve --port=8000
```

Manually verify, on the rehearsal data:

- [ ] `/` renders, with real projects and certificates
- [ ] `/projects` lists only non-archived projects
- [ ] A project **with** a case study opens at `/projects/{slug}`
- [ ] A project **without** case-study content returns 404
- [ ] An archived project is absent from the listing and 404s on its detail URL
- [ ] `/sitemap.xml` lists only URLs that resolve
- [ ] `/robots.txt` shows the correct Sitemap URL
- [ ] `/login` works and the dashboard is reachable
- [ ] Uploading a thumbnail and screenshots works (`php artisan storage:link` first)
- [ ] Replacing screenshots deletes the old files
- [ ] `/contact` submits and the message appears in `/messages`

---

## 8. Clean up

```bash
php artisan optimize:clear

mysql -h REHEARSAL_HOST -u ADMIN_USER -p \
  -e "DROP DATABASE srj_portfolio_rehearsal;"

rm .env.rehearsal
```

Keep the rehearsal dump only as long as you need it for reference.

---

## Rolling back

`2026_10_04_100500_relax_legacy_not_null_columns` is **not safely reversible** once nullable
rows exist. Its `down()` throws a `RuntimeException` listing the affected columns rather than
coercing `NULL` into `''`, because an empty string is not equivalent to `NULL` for a URL or a
file path.

**Preferred production rollback: restore the database backup taken in step 1, then redeploy the
previous application version.**

Reversible, because they only remove columns Portfolio V2 itself added:

| Migration | Reversible |
|-----------|------------|
| `100000_add_portfolio_fields_to_projects_table` | Yes |
| `100100_add_portfolio_fields_to_skills_table` | Yes |
| `100200_add_portfolio_fields_to_certificates_table` | Yes |
| `100300_create_contact_messages_table` | Yes (drops the table and its messages) |
| `100400_backfill_portfolio_slugs_and_names` | No-op by design — nothing to undo |
| `100500_relax_legacy_not_null_columns` | **No** — see above |
| `100600_add_is_admin_to_users_table` | Yes |

Note that rolling back `100600` without restoring also removes the `is_admin` flag, which would
reopen the "any authenticated account is an administrator" hole.

---

## Post-deployment verification

```bash
php artisan migrate:status          # all 2026_10_04_* show Ran
php artisan about                   # Environment: production, Debug Mode: DISABLED
php artisan tinker --execute="echo DB::connection()->getDatabaseName();"   # must be production
```

Then confirm, on the live site:

- [ ] `https://satriarangga.my.id/` renders
- [ ] `https://satriarangga.my.id/robots.txt` shows `Sitemap: https://satriarangga.my.id/sitemap.xml`
- [ ] Canonical URLs use `https://satriarangga.my.id`, not a preview hostname
- [ ] `/dashboard` still reachable with the existing administrator account
- [ ] Existing projects, technologies and certificates are all present
- [ ] Uploaded thumbnails load from `/storage/...` (requires `php artisan storage:link`)