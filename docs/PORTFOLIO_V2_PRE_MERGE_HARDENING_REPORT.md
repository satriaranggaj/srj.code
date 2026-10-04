# SRJ Portfolio V2 — Pre-Merge Hardening Report

**Repository:** https://github.com/satriaranggaj/srj.code
**Branch:** `feat/portfolio-v2`
**Audited commit:** `7ce9a644324ac235f2bb736f3e201344403d04c9`
**Report date:** 2026-10-04
**Scope:** Pre-merge hardening only. No redesign, no framework upgrade, no production data touched.

---

## 0. Important context: `main` already contains Portfolio V2

The task brief states that `feat/portfolio-v2` should be hardened and **not** merged to `main`.
That premise no longer holds, because the previous session merged Portfolio V2 into `main` on
the user's explicit instruction.

Current state before this hardening pass:

```
main              7ce9a64  (identical to feat/portfolio-v2)
origin/main       7ce9a64
origin/feat/...   7ce9a64
```

**Consequence:** every hardening commit lands on `feat/portfolio-v2` only. `main` will **not**
receive them and must be fast-forwarded separately if you want them in production. This report
does not merge.

---

## 1. Baseline

Recorded before any edit.

| Item | Value |
|------|-------|
| Branch at start | `feat/portfolio-v2` |
| HEAD at start | `7ce9a644324ac235f2bb736f3e201344403d04c9` |
| Working tree | clean |
| PHP | 8.3.35 |
| Laravel | 10.18.0 |
| Composer | 2.10.2 (`composer.json` valid) |
| Node | v23.8.0 |
| npm | 10.9.2 |
| PHPUnit | 10.3.1 |
| **Tests** | **77 passed (249 assertions), 0 failures** |
| Pint | PASS, 112 files |
| `npm ci` | Failed initially (see note) |
| `npm run build` | Success — CSS 54.11 kB (9.74 kB gzip), JS 79.52 kB (29.55 kB gzip) |
| `php artisan route:list` | 54 routes, no duplicates |

**Baseline test note:** the repository arrived with **5 pre-existing failures**
(4 × `View [auth.*] not found` on real production bugs, plus `ExampleTest` lacking
`RefreshDatabase`). Those were already fixed by the prior session, so the baseline here is
77/77 green.

**`npm ci` failure note:** the first `npm ci` failed with `EACCES` on
`node_modules/@esbuild/win32-x64/esbuild.exe`. Cause: a stale `npm run dev` / Vite process from
an earlier session was still holding the binary. The process was stopped and `npm ci` then
succeeded. This is an environment issue, not a repository defect. It also left a stale
`public/hot` file behind, which made `@vite` emit **dev-server URLs** instead of the production
bundle; that file was removed and a regression test now asserts it cannot reappear.

---

## 2. Issues verified

Every item from the brief, checked against the actual repository.

| # | Phase | Verdict | Evidence |
|---|-------|---------|----------|
| 1 | Screenshot replacement | **CONFIRMED** | `Admin\ProjectController::update()` deleted old screenshots *before* storing new ones, and detected uploads with `$request->has('screenshots')` instead of `hasFile()` |
| 2 | Visibility semantics | **PARTIALLY CONFIRMED** | `scopePublished()` = `status != archived` was already correct behaviour; only the *name* was ambiguous. Also `status != archived` would treat an unknown status as public — now an explicit allow-list |
| 3 | Case-study availability | **CONFIRMED** | `show()` only 404'd for archived. A project with no case-study content returned **200 with an empty page**. `hasCaseStudy()` also ignored `challenges`, `outcome` and `screenshots` |
| 4 | Sitemap hardening | **CONFIRMED** | Sitemap listed projects with no case study (URLs that 404), and used `now()->toAtomString()` as `lastmod` for all five static pages on every request |
| 5 | Live URL semantics | **CONFIRMED** | `getLiveUrlAttribute()` shadowed the real `live_url` column, so `$project->live_url` silently returned the fallback and the column was unreadable through the model |
| 6 | Admin authorization | **CONFIRMED** | No `is_admin`, no gate, no middleware. Any authenticated account had full write/delete on all content |
| 7 | `admin:create` command | **CONFIRMED MISSING** | Only ways to add an admin were SQL or publicly opening `/register` |
| 8 | Migration safety | **TWO REAL BUGS CONFIRMED** | See §3.2 — the backfill read an unselected column, and it overwrote an already-populated `live_url` |
| 9 | Rollback safety | **CONFIRMED** | `down()` silently coerced `NULL` → `''` |
| 10 | Migration tests | **CONFIRMED MISSING** | No schema or data migration tests existed |
| 11 | SEO / `APP_URL` | **ALREADY FIXED**, one gap found | Canonical/`og:url`/`og:image`/Twitter already derived from `APP_URL`. Gap: the case-study `og:image` used `asset()`, i.e. the **request host** |
| 12 | OG image | **CONFIRMED PRESENT, dimension mismatch documented** | `public/img/og-default.png` exists, is a valid 2000×2000 PNG. Not 1200×630 — documented, not regenerated |
| 13 | Theme audit | **CONFIRMED BROKEN** | The light-mode toggle could not change the page. Every colour is a literal `ink-*`/`bone-*` token; the only `dark:` variants in the codebase were two navbar icons |
| 14 | Self preconnect | **CONFIRMED** | `<link rel="preconnect" href="{{ asset('/') }}">` — preconnect to own origin |
| 15 | Tech-stack duplication | **CONFIRMED** | Rendering logic duplicated between `home` and `about`; a technology present in both config and the database rendered **twice** |
| 16 | Contact retention | **CONFIRMED MISSING** | `ip_address` and `user_agent` stored with no retention policy and no cleanup path |
| 17 | Contact notification | **NOT APPLICABLE** | Not a release blocker; no third-party API added. Documented as P2 |
| 18 | `robots.txt` | **CONFIRMED** | Static file with a hard-coded production sitemap URL — a footgun on every domain change |
| 19 | Dead code | **CONFIRMED (small)** | Four Blade components unreferenced. `Post`/`Category` intentionally retained |
| 20 | Accessibility | **TWO REAL ISSUES CONFIRMED** | No `<header>` (banner) landmark on any public page; project/certificate card primary links set `focus-visible:outline-none`, suppressing the focus ring |
| 21 | Performance | **ALREADY FIXED** | No iframes, lazy images, one CSS + one JS, no legacy vendor assets. Guarded by new tests |
| 22 | CI | **CONFIG VERIFIED / RUN NOT VERIFIED** | See §8 — the workflow file is correct on `main` but has **0 recorded runs** |
| 23 | Test coverage | **EXPANDED** | 77 → 286 tests |
| 24 | Code quality | **VERIFIED** | Pint, caches, route list all clean |
| 25 | README | **UPDATED** | Production env, deployment order, `admin:create`, rollback table, retention |
| 26 | No framework upgrade | **VERIFIED** | Laravel 10.18.0, PHP requirement `^8.1`, Tailwind `^3.1`, Vite `^4`, Alpine `^3.4` — all untouched |

---

## 3. Fixes implemented

### 3.1 Media replacement ordering

**Problem** — `Admin\ProjectController::update()` deleted the previous screenshot set *before*
storing the replacements, and used `$request->has()` rather than `hasFile()`.

**Root cause** — `has()` tests for a key in the input array. A `multiple` file input with
nothing selected still submits a key, so the delete branch could run with no upload at all.
Ordering meant that if a store then failed, the files were already gone while the database still
referenced them.

**Files changed** — `app/Http/Controllers/Admin/ProjectController.php`

**Fix** — strict ordering, with the old set surviving every failure mode:

1. Detect a real upload with `hasFile()`.
2. Store all new files; on partial failure, remove **only** the files this request wrote and
   return an error, leaving the database and previous files untouched.
3. Persist the new paths.
4. Only then delete the replaced managed files.

`deleteManagedImage()` was also tightened: it previously accepted any path merely *containing*
`projects/`. It now requires an exact managed-directory prefix
(`projects/thumbnails/` or `projects/screenshots/`).

**Tests added** — `tests/Feature/Admin/ProjectMediaTest.php` (11 tests, 44 assertions)

### 3.2 Two real bugs in the backfill migration

Both would have misbehaved on production data. Neither was caught by the earlier session because
no migration test existed.

**Bug A — fatal error.** `100400_backfill_portfolio_slugs_and_names` read `$project->slug` while
`select()` listed only `id, title, link`. On any real row this raises
`Undefined property: stdClass::$slug` and aborts the migration part-way.

**Bug B — data loss.** The same migration wrote `live_url` from `link` unconditionally. Any
project where the owner had already set an explicit `live_url` would have had it **overwritten**,
directly violating the "never overwrite valid existing values" rule.

**Fix** — select `slug` and `live_url`, and only fill `live_url` when it is blank.

**Tests added** — `tests/Feature/MigrationSafetyTest.php`

### 3.3 Visibility, case study and sitemap

- Added `scopePubliclyVisible()` using an explicit `whereIn([live, in_progress])` allow-list, so
  an **unknown** status is now withdrawn rather than silently public. `scopeNotArchived()` kept for
  clarity; `scopePublished()` retained as a deprecated alias delegating to the new scope.
- `hasCaseStudy()` extended to `description`, `problem`, `solution`, `challenges`, `outcome`,
  `highlights`, `screenshots`. Thumbnail deliberately excluded so missing imagery never hides a
  written case study.
- Added `hasPublicCaseStudyPage()` as the single source of truth, used by the card, the route and
  the sitemap.
- `/projects/{slug}` now 404s for archived **and** content-free projects.
- Sitemap filters to `publiclyVisible()->withPublicCaseStudy()`; static pages no longer carry a
  fabricated `lastmod`.
- `PortfolioText::slugify()` now splits camelCase, so `BarcodeIdentify` → `barcode-identify`
  instead of `barcodeidentify`.

**Tests** — `ProjectCaseStudyTest`, `ProjectVisibilityTest`, `SitemapTest`

### 3.4 `resolved_live_url`

`getLiveUrlAttribute()` → `getResolvedLiveUrlAttribute()`. The real `live_url` and `link` columns
are now readable independently through the model; only public rendering uses `resolved_live_url`.
`link` is untouched and no breaking migration was added.

**Tests** — `tests/Feature/ResolvedLiveUrlTest.php` (8 tests)

### 3.5 Admin authorization

New nullable-safe `users.is_admin` boolean. **Migration strategy:** all pre-existing rows are
marked as administrators, because before this column existed *every* authenticated account
already had full portfolio access — so nobody is locked out. Accounts created afterwards default
to non-admin, the safe direction. No email address is guessed.

Enforced by `EnsureUserIsAdmin` middleware (guest → redirect to login, non-admin → 403) and an
`access-admin` gate. Profile routes stay on plain `auth`. Self-registration now explicitly sets
`is_admin => false` so it can never grant admin even if reopened.

**Tests** — `tests/Feature/Admin/AdminAuthorizationTest.php` (32 tests, 63 assertions)

### 3.6 `admin:create`

`php artisan admin:create` prompts for name, e-mail and a **hidden** password, validates the
e-mail, enforces uniqueness and `PasswordRule::min(12)`, hashes through the model cast, sets
`is_admin = true`, never echoes the password, and fails safely on an existing e-mail.

**Tests** — `tests/Feature/AdminCreateCommandTest.php` (7 tests)

### 3.7 Migration + rollback safety

`100500_relax_legacy_not_null_columns` rewritten:

- NOT NULL detection now reads `information_schema` (MySQL/Postgres) or `PRAGMA table_info`
  (SQLite) instead of regex-parsing DDL text. The previous regex-based reconstruction is gone.
- The SQLite rebuild composes its `CREATE TABLE` from `PRAGMA table_info`, so it cannot depend on
  how a SQLite build formats its DDL.
- Idempotent: a column already nullable is skipped.
- **`down()` now throws** a `RuntimeException` naming the affected columns instead of coercing
  `NULL` → `''`. Documented in the migration itself.

**Tests** — `MigrationSafetyTest` proves both rollback behaviours.

### 3.8 Theme consistency

Code-level audit result: removing `.dark` changed **nothing** except which of two navbar icons
displayed. Every colour is a literal token with no light counterpart.

Per the brief's option B, Portfolio V2 is now intentionally dark-only. Removed: the toggle button
(desktop + mobile), the Alpine theme store, the pre-paint inline script, `<html class="dark">`, and
`darkMode: 'class'` from `tailwind.config.js` — with a comment explaining why, so nobody
reintroduces `dark:` variants that cannot work. `color-scheme: dark` is retained so native form
controls, scrollbars and select popovers stay dark.

**The dark visual identity is unchanged.** No Tailwind colour token was modified.

### 3.9 Other fixes

| Fix | Detail |
|-----|--------|
| Self preconnect removed | Phase 14 |
| Tech-stack dedupe | New `portfolio/tech-stack` component; config first, then DB skills whose name is not already listed. Duplicate rendering logic removed from `home` and `about` |
| `robots.txt` | Now served by `RobotsController` from `APP_URL`; static `public/robots.txt` deleted. A test asserts it cannot reappear |
| Banner landmark | `<header>` wraps the navbar on all public pages |
| Focus ring restored | Removed `focus-visible:outline-none` from the project and certificate card primary links |
| Case-study `og:image` | New `Project::thumbnailStoragePath()` so the preview URL is built from `APP_URL`, not the request host |
| Contact retention | `php artisan contact:prune` with `--dry-run` and `--days`; `CONTACT_MESSAGE_RETENTION_DAYS` (default 90). Never automatic |
| `--days=0` bug | `?: config()` treated an explicit `0` as unset and silently used the default. Now `!== null` |
| Dead components | `x-col`, `x-row`, `x-date-input`, `x-textarea-input` removed — all provably unreferenced |

---

## 4. Database safety

### Migrations changed

| Migration | Change in this pass |
|------------|---------------------|
| `100000_add_portfolio_fields_to_projects_table` | unchanged |
| `100100_add_portfolio_fields_to_skills_table` | unchanged |
| `100200_add_portfolio_fields_to_certificates_table` | unchanged |
| `100300_create_contact_messages_table` | unchanged |
| `100400_backfill_portfolio_slugs_and_names` | **two bug fixes**: select `slug` + `live_url`; never overwrite an existing `live_url` |
| `100500_relax_legacy_not_null_columns` | **rewritten**: information_schema/PRAGMA detection, PRAGMA-driven rebuild, fail-safe `down()` |
| `100600_add_is_admin_to_users_table` | **new** — additive boolean, backfills existing rows |

### Data touched

Only `100400` and `100600` write to pre-existing rows:

- `100400` fills **only empty** `projects.slug`, `projects.live_url` and `skills.name`. It copies
  `link` into `live_url` only when `live_url` is blank **and** `link` starts with `http`. It never
  overwrites. `down()` is a no-op.
- `100600` adds a column and sets `is_admin = true` on existing rows — deliberately preserving
  the access they already had.

No table truncated. No row deleted. No legacy column dropped. No destructive SQL.

### Backward compatibility

`projects.link` → `resolved_live_url` → `null`. Verified by test for all three states plus the
empty-string case. `certificates.link` and `skills.image` unchanged in meaning.

### Rollback limitations — stated plainly

| Migration | Reversible |
|-----------|------------|
| `100000`, `100100`, `100200`, `100300`, `100600` | Yes — only remove what Portfolio V2 added |
| `100400` | No-op by design |
| **`100500`** | **No** |

`100500` cannot be reversed once nullable rows exist. Its `down()` **refuses** and explains why,
rather than pretending a `NULL` → `''` rewrite is lossless.

**Recommended production rollback: restore the pre-migration database backup, then redeploy the
previous release.** Note that rolling back `100600` would also remove `is_admin` and reopen the
"any authenticated account is an administrator" gap.

### MySQL rehearsal — EXECUTED LOCALLY

The rehearsal was run against a **real MariaDB 10.4.32** server (the primary production target;
Laravel reports the driver as `mysql`, which is the code path that matters).

Procedure: [`docs/MYSQL_MIGRATION_REHEARSAL.md`](docs/MYSQL_MIGRATION_REHEARSAL.md).

**What was built:** a disposable database `srj_portfolio_rehearsal` containing the *exact*
pre-Portfolio-V2 schema, with the nine historical migrations marked as already run, loaded with
deliberately awkward legacy data — 12 projects including three identical titles, a blank title, a
title with symbols and accents, a very long title, and a `link` that is **not a URL**; plus
certificates, six image-only skill rows, one administrator, and a legacy post.

**Result: all 7 migrations applied cleanly, 100 verification checks, 0 failures.**

| Area | Outcome |
|------|---------|
| Row counts | Identical before and after (12 / 2 / 6 / 1 / 1) |
| Legacy `title` and `link` | Byte-for-byte identical on every row |
| Legacy `image` | Preserved on every skill row |
| Duplicate titles | `duplicate-title`, `duplicate-title-2`, `duplicate-title-3` |
| Blank title | Slug fell back to `project` without error |
| Symbol title | `PHP/Laravel 10 & Vue!` → `phplaravel-10-vue` |
| Non-URL `link` | **Not** copied into `live_url` (confirms the Bug B fix on real MySQL) |
| `live_url` copies | Every copied value matched its source `link` exactly |
| Nullable columns | `projects.link`, `certificates.link`, `skills.image` all `IS_NULLABLE = YES` via `information_schema` |
| `projects.slug` unique index | Created on a populated table without error |
| Legacy columns | All still present; nothing dropped |
| `is_admin` backfill | `1/1` existing user is an administrator — nobody locked out |
| Skill name derivation | `laravel.png`→`Laravel`, `php.png`→`PHP`, `vue-logo.png`→`Vue Logo`. Derived, never invented |

**Rollback refusal verified on MariaDB.** With a `NULL` link present, `100500->down()` threw:

> `Refusing to roll back 2026_10_04_100500_relax_legacy_not_null_columns: NULL values now exist
> in projects.link (1 NULL row(s)). Converting them to empty strings would silently alter data.
> Restore the pre-migration database backup and redeploy the previous release instead.`

and the `NULL` was confirmed still `NULL` afterwards — nothing was coerced.

**Application smoke test against MariaDB.** All public routes returned `200`, `/register`
returned `404`, and canonical/`og:image` rendered from `APP_URL`. With a dataset of 12
content-free projects, the sitemap correctly contained **only** the 5 static pages with **no**
`<lastmod>`, and all four probed case-study URLs returned `404` — precisely the Phase 3/4 rules.

**One inconsistency found and fixed during this rehearsal:** sitemap `<loc>` was built from
`route()` (the request host) while canonical, `og:image` and `robots.txt` used `APP_URL`. On a
staging hostname the sitemap would have contradicted them. Sitemap locations are now built from
`APP_URL`, with a regression test asserting a spoofed `Host` never appears.

**Cleanup verified:** the rehearsal database was dropped and all 13 pre-existing databases —
including `aplikasiwpu` and `laravel` — were confirmed still present and untouched. No fixture
scripts remain in the repository.

**Still required before deploying:** the same rehearsal against a restored copy of the *actual
production* database, because the fixture is representative rather than real.

---

## 5. Security

| Area | State |
|------|-------|
| Registration | **Disabled by default.** `ALLOW_REGISTRATION=false`; `/register` → 404. `ALLOW_REGISTRATION` untouched in `.env.example`. Registration tests kept and extended. Self-registered accounts are always non-admin. |
| Admin authorization | `users.is_admin` + `admin` middleware + `access-admin` gate. Guests → redirect, non-admins → 403. Existing accounts retain access. |
| Contact rate limiting | 5/minute per IP via `throttle:contact`, plus honeypot and server-side validation. |
| Upload validation | MIME allow-list, `max:2048` KB, explicit dimension rules for thumbnails, `max:12` files for screenshots. **Not weakened.** |
| File deletion protection | Deletion requires an exact managed-directory prefix. A shared or legacy asset is never removed — covered by a test that stores paths from three different directories and asserts which survive. |
| SQL injection | Only the query builder/Eloquent, plus one driver-aware `ALTER TABLE` with no user input. |
| XSS | Blade escaping throughout; admin flash messages via `@json()` with a Toastr method allow-list. |
| Contact privacy | IP + user agent stored deliberately for anti-spam, now with a documented 90-day default retention and an explicit prune command. |

---

## 6. Test results

Exact output from the final run:

```
Tests:    286 passed (1067 assertions)
Failures: 0
Risky:    0
```

Baseline for this pass was **77 passed (249 assertions)**. Net addition: **209 tests**.

New suites: `ProjectMediaTest` (11), `AdminAuthorizationTest` (32), `ResolvedLiveUrlTest` (8),
`ProjectVisibilityTest` (7), `SitemapTest` (8), `MigrationSafetyTest` (14),
`AdminCreateCommandTest` (7), `PruneContactMessagesCommandTest` (7), `SeoMetadataTest` (30),
`AccessibilityTest` (46), `PublicPerformanceTest` (33). `ProjectCaseStudyTest` grew from 8 to 14.

Two existing tests were **rewritten** because their assertions described behaviour that this pass
intentionally changed (`live + no case study` now 404s rather than rendering an empty page). No
test was deleted to make a suite pass.

---

## 7. Build

```
> vite v4.5.3 building for production...
✓ 54 modules transformed.

public/build/manifest.json                    0.26 kB │ gzip:   0.14 kB
public/build/assets/app-165a116d.css         51.02 kB │ gzip:   9.11 kB
public/build/assets/app-3fb1ae05.js          79.12 kB │ gzip:  29.40 kB
✓ built in 5.11s
```

| Metric | Baseline | Final |
|--------|----------|-------|
| CSS | 54.11 kB (9.74 kB gzip) | **51.02 kB (9.11 kB gzip)** |
| JS | 79.52 kB (29.55 kB gzip) | **79.12 kB (29.40 kB gzip)** |

JS is unchanged in substance (only the theme store was removed, offset by the theme-toggle
markup disappearing from the navbar). CSS shrank because the `dark:` variant rules and the theme
toggle utilities were removed.

**Nothing unexpectedly large.** No animation library. No duplicate bundle. Public asset tree
asserted under 2 MB / 20 files by test (actual: 6 files). Legacy vendor assets asserted absent.

---

## 8. CI

### CI CONFIGURATION VERIFIED

`.github/workflows/tests.yml` on `main` was fetched and read. It performs, across PHP 8.1 / 8.2 / 8.3:

- `composer install --prefer-dist --no-interaction --no-progress`
- `cp .env.example .env` + `php artisan key:generate`
- `npm ci` + `npm run build`
- `vendor/bin/phpunit`

plus a second job running `vendor/bin/pint --test`. Triggers: push to `main`, `feat/**`, `*.x`;
`pull_request`; and `workflow_dispatch` (added in this pass so it can be run on demand).

The tests use in-memory SQLite, so CI needs no database service and cannot touch a real one.

### GITHUB ACTIONS RUN NOT VERIFIED — and there is evidence of a problem

Queried the GitHub API directly:

- Repository total workflow runs: **1**
- That single run: `pages build and deployment` (dynamic), **success**, for commit `7ce9a64`
- Runs for `tests.yml` (workflow `67337127`): **0**

So the hardened workflow file **is** on `main` and is syntactically correct — it was read back
from `raw.githubusercontent.com` — yet it has never produced a run, while GitHub Pages deployed
the very same commit minutes later.

**I could not determine the cause from this environment.** `gh` is not installed, and the likely
explanations all live in settings I cannot read over the API:

1. Actions disabled or restricted for the repository.
2. A workflow-level restriction on which events may run it.
3. A previously failed/never-enabled state that has not been re-armed.

**Required follow-up:** open the Actions tab, confirm Actions are enabled for the repository, then
run `Tests` manually via `workflow_dispatch`. Do not treat CI as green until a real run exists.

### Local verification (the substitute evidence)

| Check | Result |
|-------|--------|
| `composer validate` | `./composer.json is valid` |
| `npm ci` | Success (after stopping a stale process) |
| `npm run build` | Success |
| `vendor/bin/pint --test` | **PASS, 128 files** |
| `php artisan test` | **286 passed, 1067 assertions, 0 failures** |
| `php artisan config:cache` | Success |
| `php artisan route:cache` | Success |
| `php artisan view:cache` | Success |
| `php artisan route:list` | 54 routes, **no duplicate method+URI pairs** |
| `php artisan optimize:clear` | Run; no cache files committed |

---

## 9. Remaining risks

| Risk | Impact | Action required |
|------|--------|-----------------|
| ~~MySQL never rehearsed~~ **RESOLVED LOCALLY** | Closed for MariaDB 10.4.32 with a hostile legacy fixture: 100 checks, 0 failures, plus a live application smoke test. See §4 | Repeat against a restored copy of the *real* production database before deploying |
| Production data never rehearsed | The local fixture is representative, not real. Unusual real values (very long titles, odd encodings) could behave differently | Follow the rehearsal doc step 6 assertions against a real dump. **Blocking.** |
| **GitHub Actions has 0 runs** | No automated signal on any push | Enable Actions, trigger `Tests` manually, confirm it passes |
| **`main` does not contain this hardening** | Production is missing the two migration bug fixes and the media-safety fix | Fast-forward `main` to `feat/portfolio-v2` when satisfied |
| **`APP_URL` not verified in production** | Wrong canonical/OG/sitemap/robots URLs on every page | Confirm `APP_URL=https://satriarangga.my.id` **before** `config:cache` |
| **OG image dimensions** | `public/img/og-default.png` is 2000×2000; the social convention is 1200×630. Some platforms letterbox or crop | Replace with a designed 1200×630 asset when convenient. **Not fabricated here** — the brief directs documenting rather than inventing visual content |
| **No manual visual testing** | Automated tests check structure, not appearance | Manually review all six public pages, mobile nav, and the admin forms |
| **No screen-reader pass** | Automated assertions cannot prove usability | Test with a real screen reader and keyboard-only navigation |
| **`storage:link`** | Thumbnails 404 without it | Confirm it has been run on the production host |
| **Contact message privacy** | IP + user agent retained 90 days by default | Confirm that matches your privacy policy; adjust `CONTACT_MESSAGE_RETENTION_DAYS` |
| **Email notification** | Not implemented | P2. Documented, not blocking |
| **`guzzlehttp/guzzle` / `axios`** | Declared but never called | Deferred to the framework-modernisation branch, per the version strategy |

---

## 10. Deployment checklist

```bash
# ── 0. On a REHEARSAL copy of production, not production ──────────────
#    Follow docs/MYSQL_MIGRATION_REHEARSAL.md end to end.
#    Verify: all 7 migrations Ran, row counts unchanged,
#            COUNT(*) = COUNT(DISTINCT slug), SUM(is_admin) = COUNT(*).

# ── 1. Back up production ──────────────────────────────────────────────
mysqldump --single-transaction -h HOST -u USER -p srj_portfolio \
  > ~/backups/srj_portfolio_$(date +%Y%m%d_%H%M%S).sql

# ── 2. Deploy the code ─────────────────────────────────────────────────
git pull
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# ── 3. Confirm environment BEFORE caching ──────────────────────────────
#    APP_ENV=production
#    APP_DEBUG=false
#    APP_URL=https://satriarangga.my.id   <-- drives canonical, OG, sitemap, robots.txt
#    SESSION_SECURE_COOKIE=true
#    ALLOW_REGISTRATION=false

# ── 4. Migrate (--force is required in production) ─────────────────────
php artisan migrate --force
php artisan migrate:status          # all 2026_10_04_* must show Ran

# ── 5. Required for uploaded media ─────────────────────────────────────
php artisan storage:link

# ── 6. Cache ───────────────────────────────────────────────────────────
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── 7. Verify ──────────────────────────────────────────────────────────
php artisan about                   # production, Debug Mode DISABLED
curl -sI https://satriarangga.my.id/            | head -1   # 200
curl -s  https://satriarangga.my.id/robots.txt | grep Sitemap
#   expect: Sitemap: https://satriarangga.my.id/sitemap.xml
curl -sI https://satriarangga.my.id/dashboard  | head -1   # 302 -> /login
curl -sI https://satriarangga.my.id/register   | head -1   # 404
```

**Administrators:** use `php artisan admin:create`. Do **not** set
`ALLOW_REGISTRATION=true` on a public host.

**Rollback:** restore the step-1 backup and redeploy the previous release.
`2026_10_04_100500_relax_legacy_not_null_columns` is **not** safely reversible and will refuse
rather than coerce `NULL` into `''`.

---

## 11. Definition of done

| Requirement | Status |
|-------------|--------|
| Screenshot replacement cannot destroy existing media | Done — store → persist → delete, rollback on partial failure |
| Visibility rules explicit and tested | Done — `scopePubliclyVisible()` + `ProjectVisibilityTest` |
| Empty case studies cannot create public URLs | Done — 404, covered by 5 tests |
| Sitemap contains only valid URLs | Done — every listed URL is asserted to return 200 |
| Legacy `link` fallback compatible | Done — `resolved_live_url`, 8 tests |
| Explicit admin authorization | Done — `is_admin`, middleware, gate, 32 tests |
| Registration disabled by default | Done — verified, extended, never reopened |
| Admin creation without opening registration | Done — `admin:create` + 7 tests |
| Migrations preserve existing data | Done — 2 real bugs found and fixed, 14 tests |
| Rollback limitations documented | Done — migration docblock + README table + report §4 |
| SQLite suite passes | Done — 286 passed |
| MySQL rehearsal instructions exist | Done — `docs/MYSQL_MIGRATION_REHEARSAL.md` |
| SEO still works | Done — 30 tests including spoofed-Host resistance |
| Public pages iframe-free | Done — asserted per page |
| Production build succeeds | Done — 51.02 kB CSS / 79.12 kB JS |
| Pint succeeds | Done — 128 files |
| Route/config/view cache succeeds | Done |
| README reflects implementation | Done |
| No framework upgrade mixed in | Done — Laravel 10.18.0 / PHP ^8.1 / Tailwind ^3.1 / Vite ^4 / Alpine ^3.4 unchanged |
| No production database touched | Done |
| No unverified portfolio content invented | Done |
| Final report created | This document |