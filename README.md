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
  JSON-LD (`Person`, `WebSite`, `CreativeWork`)
- **Accessibility** — semantic landmarks, skip link, visible focus ring, `aria-current` on the
  active route, labelled form fields, `prefers-reduced-motion` support
- **Dark-first theming** with an optional light mode, remembered locally, applied before first
  paint so the wrong palette never flashes

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
  Http/Controllers/
    HomeController.php              / and /about
    ProjectController.php           /projects, /projects/{slug}, /sitemap.xml
    CertificateController.php       /certificates
    ContactController.php           /contact and POST /contact
    Admin/
      DashboardController.php       dashboard overview
      SkillController.php           technologies CRUD
      ProjectController.php         projects CRUD
      CertificateController.php     certificates CRUD
      ContactMessageController.php  message inbox
  Http/Requests/
    StoreContactMessageRequest.php
    Admin/{Project,Skill,Certificate}Request.php
  Models/
    Project.php  Skill.php  Certificate.php  ContactMessage.php
    Post.php  Category.php  User.php          (Post/Category retained for compatibility)
  Support/PortfolioText.php         slug + filename helpers shared by models and migrations

config/portfolio.php                identity, contact channels, copy, stack groups

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

## Deployment Notes

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

- Run migrations on a **backup** first. Every Portfolio V2 migration is additive, but
  `2026_10_04_100400_backfill_portfolio_slugs_and_names` writes to existing rows (it only fills
  empty columns) and `2026_10_04_100500_relax_legacy_not_null_columns` alters column constraints.
- `php artisan migrate --force` is required on production because `APP_ENV=production`.
- Never run `migrate:fresh` or `db:wipe` against this database.
- The web server document root must be `public/`.
- `public/robots.txt` disallows `/dashboard`, `/profile`, `/project`, `/skill`, `/certificate`,
  `/messages` and all auth routes.
- Update the `Sitemap:` line in `public/robots.txt` if the domain changes.
- Set `SESSION_SECURE_COOKIE=true` once the site is served over HTTPS.

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
| `users` | Dashboard administrators |
| `posts`, `categories` | Legacy blog tables, retained untouched |

---

## Security Notes

- **Self-registration is closed by default.** Every authenticated account can edit all portfolio
  content, so `/register` returns 404 unless `ALLOW_REGISTRATION=true`. Existing accounts are
  unaffected.
- The contact form is validated server-side, protected by a honeypot field and rate limited per IP.
  Messages are stored in the application's own database — no third-party service and no exposed
  webhook endpoint.
- All uploaded files are validated by MIME type, size and dimensions, and stored outside the web
  root on the `public` disk.
- Deleted uploads are restricted to paths this application wrote under its own directories, so a
  shared or default image can never be removed by accident.
- External links use `target="_blank" rel="noopener noreferrer"`.
- The dashboard renders flash messages and validation errors through `json_encode` rather than raw
  string interpolation into JavaScript.
- `.env` is gitignored. No credentials, API keys or tokens are committed.
- All SQL goes through the query builder or Eloquent; the only raw SQL is driver-aware `ALTER
  TABLE` in one migration, with no user input.

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
| `tests/Feature/ProjectCaseStudyTest.php` | Valid slug renders, invalid slug 404s, archived project 404s, empty sections stay hidden, thumbnail is used as the social preview, absent URLs produce no buttons, `link` fallback works |
| `tests/Feature/ContactMessageTest.php` | Valid submission is stored, validation errors, honeypot rejection, rate limiting, metadata capture |
| `tests/Feature/Admin/ProjectManagementTest.php` | Auth gating, all index/edit pages, slug generation and uniqueness, validation, thumbnail storage, replace-and-delete, destroy, status switching, and that existing data is never overwritten |
| `tests/Feature/Auth/*` | Breeze auth flows, including that registration is closed by default |

---

## License

MIT. See [`composer.json`](composer.json).