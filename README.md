# SRJ Portfolio

Personal developer portfolio for **Satria Rangga Jati** — Full Stack Developer working on
AI-Powered Web Applications.

Built with Laravel 10, Tailwind CSS and Alpine.js. The public site is a dark-first,
content-driven portfolio; the authenticated dashboard manages every project, technology and
certificate that appears on it.

> **Portfolio V2** — see [`docs/PORTFOLIO_V2_AUDIT.md`](docs/PORTFOLIO_V2_AUDIT.md) for the full
> audit of the previous version, the findings behind every change here, and the limitations
> that are still open.

---

## Overview

The public portfolio is entirely database-driven. Adding a project in the dashboard makes it
appear on the homepage, the projects index, its own case-study page and the sitemap — with no
code changes.

Three rules shape the whole implementation:

1. **Nothing is invented.** No project, client, metric, certificate, employment record or URL is
   ever generated. If a value is missing, the UI hides the corresponding section or button.
2. **Content is editable without a deploy.** Copy lives in `config/portfolio.php`; content lives
   in the database and is managed from `/dashboard`.
3. **Additive schema changes only.** No column is dropped, and legacy data keeps working.

---

## Features

### Public

- **Home** — hero, selected work, what I build, grouped tech stack, certificates, contact CTA
- **Projects** (`/projects`) — responsive case-study cards, no iframes
- **Case studies** (`/projects/{slug}`) — overview, problem, solution, key features, tech stack,
  challenges, outcome, screenshots and links. Sections render **only** when they have content.
- **About** — about me, focus areas, how I work, technology, current direction
- **Certificates** — title, issuer, date, credential link and image
- **Contact** — verified channels plus a validated, rate-limited message form stored in the
  application's own database
- **SEO** — per-page title, meta description, canonical URL, Open Graph, Twitter/X card and
  JSON-LD (`Person`, `WebSite`, `CreativeWork`), all derived from `APP_URL`
- **Dynamic `robots.txt` and sitemap** — the sitemap lists only URLs that actually resolve, and
  the `Sitemap:` line always follows `APP_URL`
- **Accessibility** — semantic landmarks, skip link, visible focus ring, `aria-current` on the
  active route, labelled form fields, `prefers-reduced-motion` support
- **Dark-only theming** — near-black surfaces with a single restrained accent. The dark palette
  is the identity; there is no light mode, because the design tokens have no light counterpart
  and a non-functional toggle is worse than none

### Dashboard (`/dashboard`, authentication required)

- Content counts and a recent activity overview
- **Projects** — title, slug, short description, description, thumbnail, type, tech stack, live and
  GitHub URLs, featured flag, status, sort order, and full case-study content
- **Technologies** — name, category, reference URL, optional logo
- **Certificates** — title, issuer, issue date, credential URL, description, image
- **Messages** — an inbox for the public contact form with read/unread and delete
- Profile and password management

Every form validates through a `FormRequest`. Uploads are MIME-checked and size-capped, stored on
the `public` disk, and the previous file is deleted only when it is one this application wrote.

---

## Architecture

```
app/
  Console/Commands/
    CreateAdminUser.php             admin:create
    PruneContactMessages.php        contact:prune
  Http/
    Controllers/
      HomeController.php              / and /about
      ProjectController.php           /projects, /projects/{slug}, /sitemap.xml
      CertificateController.php       /certificates
      ContactController.php           /contact and POST /contact
      RobotsController.php            /robots.txt (Sitemap URL derived from APP_URL)
      Admin/
        DashboardController.php       dashboard overview
        SkillController.php           technologies CRUD
        ProjectController.php         projects CRUD
        CertificateController.php     certificates CRUD
        ContactMessageController.php  message inbox
    Middleware/
      EnsureUserIsAdmin.php          the `admin` middleware
    Requests/
      StoreContactMessageRequest.php
      Admin/{Project,Skill,Certificate}Request.php
  Models/
    Project.php  Skill.php  Certificate.php  ContactMessage.php
    Post.php  Category.php  User.php          (Post/Category retained for compatibility)
  Providers/
    AuthServiceProvider.php          defines the `access-admin` gate
  Support/PortfolioText.php         slug + filename helpers shared by models and migrations

config/portfolio.php                identity, contact channels, copy, stack groups, retention

resources/views/
  layouts/portfolio.blade.php       public shell (registered as <x-layouts.portfolio>)
  components/portfolio/             navbar, footer, seo, project-card, skill-badge,
                                    certificate-card, contact-method, capability-card,
                                    timeline-item, section-heading, page-header, button,
                                    social-link, icon, empty-state
  portfolio/                        home, about, contact, projects/{index,show},
                                    certificates/index, sitemap
  Admin/                            dashboard (untouched Breeze layout + new forms)
  layouts/{app,guest,navigation}    admin (unchanged)
```

**Two frontend stacks, deliberately.** The public site is Blade + Tailwind + Alpine.
The admin dashboard keeps its Breeze/Tailwind layout plus jQuery, Toastr and SweetAlert2, which
power the delete-confirmation flow. Only jQuery, Toastr and `admin.js` remain under `public/`; the
~32 MB of legacy Bootstrap, Font Awesome, CKEditor, Lightbox, Swiper, hand-written SCSS and
duplicated jQuery copies were removed after verifying zero references.

### Backward compatibility

| Legacy field | Treatment |
|--------------|-----------|
| `projects.link` | Kept. `live_url ?? link` is the read path via the `liveUrl` accessor. Existing values are copied into `live_url` by an idempotent migration and are never overwritten. |
| `certificates.link` | Kept as the credential URL. |
| `skills.image` | Kept. Rows without a `name` fall back to a name derived from the file name. |
| All 18 admin route names | Preserved verbatim — no Blade reference or bookmark breaks. |
| `posts` / `categories` tables | Untouched. No data is destroyed. |

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 10 (`^10.10`) |
| PHP | `^8.1` (CI: 8.1, 8.2, 8.3) |
| Frontend | Blade, Tailwind CSS 3, Alpine.js 3, Vite 4 |
| Auth | Laravel Breeze (Blade) |
| Admin JS | jQuery, Toastr, SweetAlert2 |
| Icons | Inline SVG (`<x-portfolio.icon>`) — no icon font |
| Database | MySQL in production, in-memory SQLite for tests |
| Tests | PHPUnit 10 |

No runtime frontend dependency is loaded from a third-party CDN except SweetAlert2 in the admin.

---

## Requirements

- PHP **8.1+** with `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `gd`
- Composer 2
- Node.js 18+ and npm
- MySQL 8 (or MariaDB 10.4+)

---

## Local Installation

```bash
git clone https://github.com/satriaranggaj/srj.code.git
cd srj.code

composer install
npm install

cp .env.example .env
php artisan key:generate
```

### Environment Setup

Edit `.env`:

```dotenv
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=srj_portfolio
DB_USERNAME=root
DB_PASSWORD=
```

Optional keys — every one is disabled by default so nothing unverified is ever published:

| Key | Purpose |
|-----|---------|
| `PORTFOLIO_EMAIL` | Enables the Email contact channel |
| `PORTFOLIO_RESUME_URL` | Résumé link |
| `PORTFOLIO_TWITTER_SITE` | `twitter:site` meta tag |
| `SESSION_SECURE_COOKIE` | Set to `true` on HTTPS deployments |
| `ALLOW_REGISTRATION` | Set to `true` only while creating an extra admin account |

### Database Setup

```bash
php artisan migrate
php artisan storage:link     # required for uploaded thumbnails and logos
```

`storage:link` is essential: the dashboard stores uploads on the `public` disk and the public site
reads them from `/storage`.

### Development

```bash
php artisan serve
npm run dev          # in a second terminal
```

### Build

```bash
npm run build         # production assets into public/build
```

### Testing

```bash
php artisan test
# or
vendor/bin/phpunit
```

The suite runs against an **in-memory SQLite** database (`phpunit.xml`), so neither CI nor a local
run can touch a real database.

---

## Production Environment

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://satriarangga.my.id
SESSION_SECURE_COOKIE=true
ALLOW_REGISTRATION=false
```

> **`APP_URL` MUST be correct before you run `php artisan config:cache`.**
> The canonical URL, `og:url`, `og:image`, the Twitter image, every sitemap entry and the
> `Sitemap:` line in `robots.txt` are all derived from `APP_URL` — deliberately, so a spoofed
> `Host` header or a preview hostname can never produce canonical URLs for another domain.
> A wrong `APP_URL` therefore publishes wrong metadata to every search engine and social
> platform, and config caching will freeze it.

Never put real passwords, tokens or API keys in this file, in `.env.example`, or anywhere else
in the repository.

### Deployment

> **Production migrations must only be run after a database backup and a MySQL rehearsal.**
> The automated test suite runs on in-memory SQLite, which does **not** prove the migrations
> work on MySQL. Follow [`docs/MYSQL_MIGRATION_REHEARSAL.md`](docs/MYSQL_MIGRATION_REHEARSAL.md)
> against a restored copy first.

```bash
# 1. Back up the production database BEFORE pulling.
mysqldump --single-transaction -h HOST -u USER -p srj_portfolio > backup.sql

# 2. Deploy the code.
git pull
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# 3. Migrate (requires --force because APP_ENV=production).
php artisan migrate --force

# 4. Required for uploaded thumbnails and logos to load from /storage.
php artisan storage:link

# 5. Cache. config:cache freezes APP_URL, so confirm step "Production Environment" first.
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Never run `migrate:fresh`, `db:wipe`, or `migrate:rollback` against production.

### Creating an administrator

```bash
php artisan admin:create
```

Prompts for name, e-mail and a hidden password, validates them, hashes the password and creates
an account with administrator access. It never prints the password.

Do **not** open `/register` on a production website to add an administrator. `ALLOW_REGISTRATION`
is a development/emergency escape hatch only: it makes `/register` reachable, and while it is
reachable any visitor can create an account (which is always non-admin, but still gets an
account on your system). If you must use it, set it back to `false` immediately afterwards.

```bash
# Emergency only
ALLOW_REGISTRATION=true php artisan serve   # local, never on a public host
```

### Rollback

Most Portfolio V2 migrations are reversible because they only add columns Portfolio V2 itself
introduced. One is not:

| Migration | Reversible |
|-----------|------------|
| `2026_10_04_100000_add_portfolio_fields_to_projects_table` | Yes |
| `2026_10_04_100100_add_portfolio_fields_to_skills_table` | Yes |
| `2026_10_04_100200_add_portfolio_fields_to_certificates_table` | Yes |
| `2026_10_04_100300_create_contact_messages_table` | Yes (drops the table) |
| `2026_10_04_100400_backfill_portfolio_slugs_and_names` | No-op by design |
| `2026_10_04_100500_relax_legacy_not_null_columns` | **No** |
| `2026_10_04_100600_add_is_admin_to_users_table` | Yes |

`100500` cannot be safely reversed once nullable rows exist. Its `down()` throws a
`RuntimeException` naming the affected columns instead of coercing `NULL` into `''`, because an
empty string is not equivalent to `NULL` for a URL or a file path.

**Preferred production rollback: restore the database backup taken immediately before
migrating, then redeploy the previous application version.** Do not rely on `migrate:rollback`
for this migration, and note that rolling back `100600` would also remove the `is_admin` flag and
reopen the "any authenticated account is an administrator" gap.

### Other deployment notes

- The web server document root must be `public/`.
- `robots.txt` is served by the application (`GET /robots.txt`) so its `Sitemap:` line always
  follows `APP_URL`. There must be **no** static `public/robots.txt`: the web server would serve
  that file directly and the route would never run.
- Set `SESSION_SECURE_COOKIE=true` once the site is served over HTTPS.
- Optional contact channels are disabled by default, so an unverified URL is never published.

### Maintenance commands

```bash
# Delete contact messages older than the configured retention window (default 90 days).
php artisan contact:prune --dry-run   # report only
php artisan contact:prune             # actually delete

# Never run automatically; retention is always an explicit operator decision.
CONTACT_MESSAGE_RETENTION_DAYS=90
```

---

## Project Structure

```
app/                     Controllers, FormRequests, Models, Providers, Support
config/portfolio.php     All non-database content and configuration
database/
  factories/             ProjectFactory, SkillFactory, CertificateFactory
  migrations/            Historical migrations plus five additive Portfolio V2 migrations
  seeders/
docs/PORTFOLIO_V2_AUDIT.md
public/
  img/og-default.png     Default social preview image
  favicon.svg
  libraries/             jQuery + Toastr (admin only)
  script/admin.js        Delete confirmation (admin only)
resources/
  css/app.css            Tailwind layers + Portfolio V2 base styles
  js/app.js              Alpine, theme store, scroll reveal
  views/                 See Architecture above
routes/web.php           Public + admin routes
routes/auth.php          Breeze auth routes
tests/                   Feature tests for every public route and the admin CRUD
```

### Database tables

| Table | Purpose |
|-------|---------|
| `projects` | Portfolio projects and their case-study content |
| `skills` | Technologies, grouped by category |
| `certificates` | Certificates and credentials |
| `contact_messages` | Messages submitted through the public contact form |
| `users` | Dashboard administrators (`is_admin`) |
| `posts`, `categories` | Legacy blog tables, retained untouched |

---

## Security Notes

- **Explicit administrator authorization.** `users.is_admin` plus an `admin` middleware and an
  `access-admin` gate protect the dashboard and every content-management route. Guests are
  redirected to `/login`; authenticated non-admins receive `403`. Profile routes stay available
  to any signed-in account because they only affect that account.
- **Existing accounts keep their access.** The `is_admin` migration marks pre-existing rows as
  administrators, because before the column existed every authenticated account already had full
  portfolio access. Accounts created afterwards default to non-admin. See the migration for the
  rationale.
- **Self-registration is closed by default.** `/register` returns 404 unless
  `ALLOW_REGISTRATION=true`, and even then a self-registered account is always non-admin. Use
  `php artisan admin:create` instead.
- The contact form is validated server-side, protected by a honeypot field and rate limited per
  IP. Messages are stored in the application's own database — no third-party service and no
  exposed webhook endpoint. Visitor IP address and user agent are stored alongside the message for
  anti-spam purposes and are pruned on a configurable retention window via `contact:prune`.
- All uploaded files are validated by MIME type, size and dimensions, and stored outside the web
  root on the `public` disk. Replacement is ordered store → persist → delete, so a failed upload
  can never destroy the existing media. Deleted uploads are restricted to paths this application
  wrote under `projects/thumbnails/` and `projects/screenshots/`.
- External links use `target="_blank" rel="noopener noreferrer"`.
- The dashboard renders flash messages and validation errors through `@json()` rather than raw
  string interpolation into JavaScript.
- `.env` is gitignored. No credentials, API keys or tokens are committed.
- All SQL goes through the query builder or Eloquent; the only raw SQL is driver-aware
  `ALTER TABLE` in one migration, with no user input.

### Reporting a vulnerability

Open a GitHub issue, or contact me through the channels on [satriarangga.my.id](https://satriarangga.my.id).

---

## Adding your strongest projects

The strongest work belongs in the dashboard rather than in a seeder, so nothing is committed as
data that cannot be verified. For each project:

1. **Title** and **Slug** — the slug becomes the public URL (`/projects/<slug>`). Leaving it blank
   generates one from the title.
2. **Short description** — one or two sentences. Used on cards and as the social preview text.
3. **Live URL** / **GitHub URL** — only if the URL actually exists. Each button renders only when
   its URL is present.
4. **Tech stack** — comma-separated.
5. **Thumbnail** — 16:9. Without one, a designed placeholder is rendered; a screenshot is never
   faked.
6. **Status** — `live`, `in_progress` or `archived`. Archived projects disappear from the public
   site and return 404.
7. **Featured** — controls whether the project appears in Selected work on the homepage.
8. **Case study** — Problem, Solution, Key features, Challenges and Outcome. Any field left empty
   removes its section entirely.

---

## Testing

Feature coverage:

| File | Covers |
|------|--------|
| `tests/Feature/PublicPagesTest.php` | All five public routes return 200, render the shared chrome and SEO metadata, never emit an iframe, hide disabled channels, and the sitemap lists case studies |
| `tests/Feature/ProjectCaseStudyTest.php` | Valid slug renders; invalid slug, archived project and content-free project all 404; empty sections stay hidden; thumbnail used as social preview; absent URLs produce no buttons; `link` fallback works |
| `tests/Feature/ProjectVisibilityTest.php` | `live` / `in_progress` / `archived` / unknown-status visibility across homepage, listing, detail route and sitemap; admin listing still shows archived work |
| `tests/Feature/ResolvedLiveUrlTest.php` | `live_url` → `link` → null resolution, both real columns remaining independently readable, legacy rows rendering correctly |
| `tests/Feature/SitemapTest.php` | Valid XML, all static pages present, no fabricated `lastmod` on static pages, archived and content-free projects excluded, and every listed URL actually resolving |
| `tests/Feature/SeoMetadataTest.php` | Per-page titles, canonical / `og:url` / `og:image` / Twitter tags, valid JSON-LD, `APP_URL`-derived URLs resisting a spoofed `Host` header, robots.txt, heading order |
| `tests/Feature/AccessibilityTest.php` | Skip link, landmarks, heading order, labelled fields, alt text, lazy/eager image loading, `aria-current`, focus visibility, no nested interactive elements, external link `rel` |
| `tests/Feature/PublicPerformanceTest.php` | No iframes, no Bootstrap/jQuery/icon fonts, no third-party font requests, exactly one stylesheet and script, bundle size ceilings, legacy assets absent, query budget |
| `tests/Feature/ContactMessageTest.php` | Valid submission stored, validation errors, honeypot rejection, rate limiting, metadata capture |
| `tests/Feature/Admin/ProjectManagementTest.php` | Slug generation and uniqueness, validation, thumbnail storage, replace-and-delete, destroy, status switching, existing data never overwritten |
| `tests/Feature/Admin/ProjectMediaTest.php` | Screenshot upload, preservation without upload, replacement, delete-after-success, validation-failure safety, and that files outside the managed directory are never deleted |
| `tests/Feature/Admin/AdminAuthorizationTest.php` | Guest redirect, non-admin 403, admin access, CRUD enforcement for non-admins, self-registered accounts never becoming admins |
| `tests/Feature/AdminCreateCommandTest.php` | `admin:create` success, hashing, dashboard access, duplicate/invalid email, weak password, missing fields |
| `tests/Feature/PruneContactMessagesCommandTest.php` | Retention window, configurability, dry run, override, rejection of a nonsense window, and that deletion is never automatic |
| `tests/Feature/MigrationSafetyTest.php` | Schema after migrate, legacy columns never dropped, relaxed columns nullable, legacy values surviving the backfill, idempotency, unique slug generation, `is_admin` backfill, rollback refusal |
| `tests/Feature/Auth/*` | Breeze auth flows, including that registration is closed by default |

> The suite runs on **in-memory SQLite** (see `phpunit.xml`), so neither CI nor a local run can
> touch a real database. That does **not** prove MySQL compatibility — see
> [`docs/MYSQL_MIGRATION_REHEARSAL.md`](docs/MYSQL_MIGRATION_REHEARSAL.md).

---

## License

MIT. See [`composer.json`](composer.json).