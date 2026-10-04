# SRJ Portfolio V2 — Repository Audit

**Repository:** https://github.com/satriaranggaj/srj.code
**Branch:** `feat/portfolio-v2`
**Audit date:** 2026-10-04
**Auditor scope:** Full read of `app/`, `routes/`, `database/`, `resources/`, `public/`, `tests/`, `config/`, `.github/`, dependency manifests.

> This document is the source-of-truth audit referenced by the Portfolio V2 implementation.
> All findings below were verified by reading the actual files, not inferred from the task brief.
>
> **Sections 1–17 describe the state of the repository *before* the redesign.**
> **Section 18 records what was actually changed, and Section 19 the final verification.**

---

## 1. Executive summary

The repository is a **Laravel 10 portfolio/blog skeleton** with an **authenticated Breeze admin dashboard**.
It is functional but the public site is built on a hand-rolled legacy stack (Bootstrap 5 + jQuery + plain SCSS +
Font Awesome), while the admin dashboard already uses **Tailwind CSS 3 + Alpine.js + Vite**.

Key conclusion: **the two halves of the app use two different frontend stacks.** Portfolio V2 therefore
*unifies the public side onto the stack the admin already uses* (Tailwind + Alpine + Vite), and removes
the legacy public stack only where it is provably unused. The admin dashboard is left functionally intact.

### Most important findings

| # | Severity | Finding |
|---|----------|---------|
| 1 | **Critical** | Public `/register` route is enabled → **any visitor can create an account and reach `/dashboard`**, which allows editing/deleting all portfolio content. |
| 2 | **High** | `PasswordResetLinkController`, `NewPasswordController`, `ConfirmablePasswordController`, `EmailVerificationPromptController` return `view('auth.*')`, but the views live in `resources/views/Admin/auth/`. **Forgot-password, reset-password, confirm-password and verify-email pages are HTTP 500 in production.** |
| 3 | **High** | Homepage feedback form POSTs directly from the browser to a **Google Apps Script web-app URL** embedded in public JavaScript. No server-side validation, no spam protection, no rate limit, no error surface, and the endpoint is an **unauthenticated open relay** that anyone can spam. |
| 4 | **High** | `public/script/main.js` unconditionally does `document.forms['submit-to-google-sheet'].addEventListener(...)`. The admin layout loads this file, so **every admin page throws a JavaScript `TypeError`**. |
| 5 | **High** | `resources/views/Admin/projects/form.blade.php` contains an inline `<script>` calling `document.getElementById("image").addEventListener(...)`, but no element with `id="image"` exists on that page → **JavaScript error on the project admin form**. |
| 6 | **Medium** | Project previews use `<iframe src="{{ $project->link }}">` → 3 cross-origin iframes per page, heavy, unstyled, blocked by many `X-Frame-Options` headers, and hostile to Core Web Vitals. |
| 7 | **Medium** | `Project::rev()`, `Project::lastProject()`, `Post::rev()`, `Post::lastPost()` all call `static::all()` then reverse in PHP → **unbounded full-table loads**. |
| 8 | **Medium** | `Admin.projects.index` runs `Project::query()` **and** `Project::all()` for the same data; same for skills and certificates. Double query per index page. |
| 9 | **Medium** | `assets('storage/' . $skill->image)` is used on the homepage, but `.env.example` ships `FILESYSTEM_DISK=local` (not `public`). Skill logos break unless the production `.env` overrides it. |
| 10 | **Medium** | No `<meta name="description">`, no canonical URL, no Open Graph, no Twitter card, no JSON-LD, no sitemap on any public page. |
| 11 | **Medium** | `robots.txt` is `User-agent: * / Disallow:` — allows everything including `/dashboard`, `/profile`, `/login`. |
| 12 | **Low** | `app/Models/Post_.php` declares `class post` (lowercase, wrong file name) → **PSR-4 violation**, skipped by Composer autoloader on every install. |
| 13 | **Low** | Public profile photo `public/frontend/img/photo-profil/IMG_1666.jpg` is **5.93 MB**. |
| 14 | **Low** | Google Fonts loads **both `Caveat` and `Inter`** with weights 100–900 (10 weights) on every public page. `Caveat` is used once. |
| 15 | **Low** | CI workflow `.github/workflows/tests.yml` triggers on `master`; the repo default branch is `main` → **CI has never run for this repo**. |

---

## 2. Current architecture

### 2.1 Stack

| Layer | Technology | Version (pinned range) |
|-------|-----------|----------------------|
| Framework | Laravel | `^10.10` |
| PHP | — | `^8.1` (CI matrix: 8.1, 8.2) |
| Auth | Laravel Breeze (Blade scaffolding) | `^1.29` |
| API tokens | Laravel Sanctum | `^3.2` |
| HTTP client | Guzzle | `^7.2` |
| Dev tooling | Pint, Sail, Collision, Ignition, PHPUnit, Faker, Mockery | see `composer.json` |
| Public CSS | **Bootstrap 5** (vendored) + hand-written SCSS + compiled CSS | `public/frontend/` |
| Public JS | **jQuery 3.7.0** + hand-written `main.js` | `public/script/` |
| Public icons | **Font Awesome 6** (vendored, ~2 000 SVG files) | `public/frontend/libraries/fontawesome` |
| Admin CSS/JS | **Tailwind CSS 3** + **Alpine.js 3** + `laravel-vite-plugin` | `resources/css`, `resources/js` |
| Admin JS extras | jQuery, Toastr, **SweetAlert2 (CDN)**, Bootstrap bundle, Swiper (CDN) | `resources/views/layouts/app.blade.php` |
| Build | Vite 4, PostCSS 8, Autoprefixer 10 | `vite.config.js` |

**There is no Vue, React, Inertia, Livewire, or npm runtime dependency for jQuery.** All frontend libraries are
committed as loose files under `public/` rather than managed by npm.

### 2.2 Dual-stack reality (critical to understand before refactoring)

```
PUBLIC PAGES                          ADMIN PAGES
────────────                          ───────────
layouts/main.blade.php               layouts/app.blade.php
  ├ bootstrap.min.css (vendored)       ├ @vite → Tailwind 3 + Alpine 3
  ├ fontawesome all.min.css            ├ jQuery + Toastr + SweetAlert2 + Bootstrap
  ├ frontend/style/*.css (SCSS out)    └ layouts/navigation.blade.php (Alpine)
  ├ script/main.js
  └ libraries/jquery
```

This is why the brief says *"do not break the existing admin dashboard just to clean frontend dependencies."*
The correct move is to **replace the public stack** and **leave the admin stack alone**.

### 2.3 Composer autoload note

`composer.json` autoloads a global helper file:

```json
"autoload": { "files": ["app/Helpers/general.php"] }
```

`app/Helpers/general.php` defines a single global function `greeting()` that calls `date_default_timezone_set('Asia/Jakarta')`
as a side effect and `echo`s a result. It is used once, in `Admin/dashboard.blade.php`.

---

## 3. Routes

### 3.1 Public (`routes/web.php`)

| Method | URI | Name | Controller | View |
|--------|-----|------|------------|------|
| GET | `/` | `home` | `HomeController@index` | `Home.home` |
| GET | `/about` | `about` | `HomeController@bio` | `Home.about` |
| GET | `/projects` | `project` | `HomeController@projects` | `Home.projects` |
| GET | `/certificates` | `certificate` | `HomeController@certificate` | `Home.certificates` |
| GET | `/contact` | `contact` | `HomeController@contact` | `Home.contact` |

All five are thin, all use `layouts.main`, all are reachable and working (modulo the issues above).

### 3.2 Authenticated admin (`auth` middleware only — **no role check**)

| Method | URI | Name |
|--------|-----|------|
| GET | `/dashboard` | `dashboard` |
| GET/PATCH/DELETE | `/profile` | `profile.edit` / `profile.update` / `profile.destroy` |
| GET | `/skill` | `skill.index` |
| GET | `/skill/create` | `skill.create` |
| POST | `/skill` | `skill.store` |
| GET | `/skill/{skill}/edit` | `skill.edit` |
| PUT | `/skill/{skill}` | `skill.update` |
| DELETE | `/skill/{skill}` | `skill.destroy` |
| GET | `/project` | `project.index` |
| GET | `/project/create` | `project.create` |
| POST | `/project` | `project.store` |
| GET | `/project/{project}/edit` | `project.edit` |
| PUT | `/project/{project}` | `project.update` |
| DELETE | `/project/{project}` | `project.destroy` |
| GET | `/certificate` | `certificate.index` |
| GET | `/certificate/create` | `certificate.create` |
| POST | `/certificate` | `certificate.store` |
| GET | `/certificate/{certificate}/edit` | `certificate.edit` |
| PUT | `/certificate/{certificate}` | `certificate.update` |
| DELETE | `/certificate/{certificate}` | `certificate.destroy` |

Every one of the 18 admin actions points at `DashboardController` with a suffixed method name
(`indexSkill`, `storeSkill`, …). Route names are referenced from Blade (`route('skill.index')`,
`route('project.edit', $id)`, …) and **must be preserved**.

### 3.3 Auth (`routes/auth.php`, Breeze)

`register`, `login`, `forgot-password`, `reset-password/{token}`, `verify-email`,
`verify-email/{id}/{hash}`, `confirm-password`, `email/verification-notification`, `password`, `logout`.

**`register` is publicly reachable.** See finding #1.

### 3.4 Dead routes

None. There are no `/blog`, `/blog/{slug}`, `/{slug}` or `post.*` routes — although `Home/posts.blade.php`
and `Home/post.blade.php` exist and reference `route`-less hardcoded URLs like `href="/{{ $post->slug }}"`.
Those two views are unreachable dead code.

---

## 4. Models

| Model | Table | Columns | Notable issues |
|-------|-------|---------|----------------|
| `App\Models\User` | `users` | `name`, `email`, `password`, `email_verified_at`, `remember_token`, timestamps | `MustVerifyEmail` is commented out. Sanctum's `HasApiTokens` applied but **no API routes exist** (`routes/api.php` defines nothing). |
| `App\Models\Project` | `projects` | `id`, `title`, `link`, timestamps | `$fillable = ['title','link']`. `rev()` and `lastProject()` load the entire table. No scopes, no casts, no `HasFactory` usage. |
| `App\Models\Skill` | `skills` | `id`, `image`, timestamps | `$fillable = ['image']`. Only an image path — **no name, no category**. The homepage renders raw logo images in a CSS marquee. |
| `App\Models\Certificate` | `certificates` | `id`, `title`, `link`, timestamps | `$fillable = ['title','link']`. No issuer, no date, no image. |
| `App\Models\Post` | `posts` | `category_id`, `slug`, `thumbnail`, `date`, `title`, `excerpt`, `body`, `published_at`, timestamps | `$guarded = ['id']`. `lastPost()`/`rev()` load all rows. Only consumer is `HomeController@index`, and the homepage never renders `$posts`. |
| `App\Models\Category` | `categories` | `name`, `slug`, timestamps | `$guarded = ['id']`. Only referenced by `Post::category()`. |
| `App\Models\post` (`Post_.php`) | *none* | static array of 5 lorem-ipsum posts | **PSR-4 violation**, skipped by Composer autoloader. Zero references anywhere. |

`Project` is the only model with hand-rolled query helpers (`rev`, `lastProject`) and they are the direct cause
of the full-table-load performance problems.

---

## 5. Database schema (existing, verified from migrations)

```
users                      id, name, email, password, email_verified_at, remember_token, timestamps
password_reset_tokens      email, token, created_at
failed_jobs                id, uuid, connection, queue, payload, attempts, reserved_at, available_at, created_at
personal_access_tokens     id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, timestamps
categories                 id, name (unique), slug (unique), timestamps
posts                      id, category_id, slug (unique), thumbnail, date, title, excerpt, body,
                           published_at (nullable), timestamps
skills                     id, image, timestamps
projects                   id, title, link, timestamps
certificates               id, title, link, timestamps
```

Engine per `.env.example`: **MySQL**. `phpunit.xml` had the SQLite overrides commented out, so the test suite
was attempting to run against the developer's real MySQL database.

### 5.1 Data model gaps for Portfolio V2

| Need | Present today | Gap |
|------|---------------|-----|
| Project thumbnail | ✗ | no `thumbnail` column |
| Project slug | ✗ | no `slug` — required for `/projects/{slug}` |
| Project description / case study | ✗ | only `title` + `link` |
| Project tech stack | ✗ | ✗ |
| Project type | ✗ | ✗ |
| Project GitHub URL | ✗ | ✗ |
| Featured flag | ✗ | ✗ |
| Status / sort order | ✗ | ✗ |
| Certificate issuer / date / image | ✗ | only `title` + `link` |
| Skill name / category | ✗ | only `image` path |
| Contact messages | ✗ | the feedback form bypasses the database entirely |

Every gap is filled **additively** in Portfolio V2. No column is dropped.

---

## 6. Public pages (current state)

### 6.1 `/` — Homepage (`Home/home.blade.php`, 135 lines)
Sections: Bio (6-paragraph essay) → Passion (essay + CSS marquee of skill logos) → Featured Project
(3 `<iframe>`s) → Feedback (Google Sheets form).

Issues: third-person biography on the homepage; `<center>` used for headings; duplicate skill marquee
(each logo rendered **twice** to make the loop seamless); 6 KB of raw HTML per project; Bootstrap classes
mixed with custom classes.

### 6.2 `/about` (`Home/about.blade.php`)
Third-person biography again, with `<br><br>` used for paragraph breaks and `style="text-align: justify;"`
inline. Loads a **5.93 MB** JPEG.

### 6.3 `/projects` (`Home/projects.blade.php`)
31 lines. `<center>` heading, `<iframe>` per project, "View Project" link.

### 6.4 `/certificates` (`Home/certificates.blade.php`)
A `<p>` + a `<button>` **wrapping an `<a>`** (invalid interactive nesting) per certificate, with a repeated
inline SVG arrow. No image, no issuer, no date.

### 6.5 `/contact` (`Home/contact.blade.html`)
4 identical blocks, each: `<p>Label</p>` + `<button><span><a>value</a></span><span><svg…/></span></button>`.
The same arrow `<svg>` is duplicated 4×. Stray `</i>` closing tags after the anchor text with no opening `<i>`.
LinkedIn is displayed prominently (see §11).

### 6.6 Accessibility & SEO audit of the current public pages

| Check | Result |
|-------|--------|
| `<meta name="description">` | **absent on every page** |
| `<title>` | `"SRJ \| Home"` — no per-page description, brand-first ordering |
| Canonical URL | **absent** |
| Open Graph / Twitter card | **absent** |
| JSON-LD structured data | **absent** |
| Heading order | `<h1>` name → `<h3>` section headings → `<h5>` project titles (skips levels) |
| `<center>` (obsolete) | used on `/`, `/about`, `/projects`, `/certificates`, and in `partials/footer` |
| Interactive nesting | `<a>` inside `<button>` on `/contact` and `/certificates` |
| Mobile menu | `<input type="checkbox"/>` + `<span>×3` burger; `window.onscroll` adds a **new `scroll` listener on every scroll event** (listener leak) |
| Skip link | **absent** |
| `aria-current` on active nav | **absent** |
| Image `alt` text | `alt="skill-logo"` (meaningless), `alt=""` |
| Colour contrast | body text `#…` on white — needs verification, navbar uses custom CSS |
| Reduced motion | **not respected** — the skill marquee animates unconditionally |
| Focus visibility | custom CSS, no explicit `:focus-visible` |
| Form labels | present, but `<input type="subject">` is an **invalid input type** (`subject` falls back to `text`) |
| Loading/error state | `.btn-loading` shown via `classList.toggle` on both buttons — both end up visible/invisible inconsistently; failure is only `console.error` |
| Favicon | `public/favicon.ico` is **0 bytes**; layout points at `frontend/img/icon/srj-icon.png` instead |

---

## 7. Dashboard features

`DashboardController` (317 lines) handles five unrelated responsibilities:

1. `index()` → dashboard greeting.
2. **Skill CRUD** (6 methods) — upload logo to `skills-logo/` on the `public` disk, replace-and-delete old image.
3. **Project CRUD** (6 methods) — validates only `link` + `title`, both `required`, **no URL validation**.
4. **Certificate CRUD** (6 methods) — validates only `link` + `title`, both `required`.
5. **Post CRUD** (6 methods) — **entirely commented out**, 90 lines of dead code.

`Admin/posts/index.blade.php` and `Admin/posts/form.blade.php` exist but no route or live controller method
reaches them.

### 7.1 How Skills / Projects / Certificates are actually managed today

- **Skills:** create = upload image only (no name field at all). Update = optional image, deletes the previous
  file. Delete = deletes the file then the row, wrapped in a bare `catch (\Exception $e)`.
- **Projects:** create/update = `link` + `title`, both required. **No image, no description, no slug.**
- **Certificates:** create/update = `link` + `title`, both required. **No image, no issuer, no date.**
- **Listing:** every index runs the paginated `AppUtility::listView()` query **and** `Model::all()`, passes
  both to the view, and the view only ever renders the `::all()` copy. The pagination object is built and discarded.
- **Delete confirmation:** `<x-danger-button data-toggle="delete-button" href="…">` + `public/script/admin.js`
  (jQuery + SweetAlert2) which repoints `#form-delete` (a form in `layouts/app.blade.php` with `action=""`) and
  submits it. This chain **must not be broken.**
- **Flash messages:** controllers `->with('message', [['success', '…']])`; `layouts/app.blade.php` iterates
  `Session::get('message')` and calls `toastr['{{ $message[0] }}']('{{ $message[1] }}')`. Note this is a
  **raw string interpolation into JS** — a stored-XSS vector if any admin-editable text ever reaches a flash message.
- **Layout dependency:** `layouts/app.blade.php` loads jQuery, Toastr, SweetAlert2 (CDN), Swiper (CDN),
  Bootstrap bundle, `main.js`, plus `frontend/style/navigation/navigation.css`.

---

## 8. Dependencies

### 8.1 Unused / removable from the **public** side

| Asset | Size | Verdict |
|-------|------|---------|
| `public/libraries/ckeditor/**` (incl. a **6.17 MB** `ckeditor.js.map`) | ~7.3 MB | **Unused.** Zero references in any Blade file. |
| `public/libraries/lightbox2/**` (full Grunt source, `bower.json`, `ROADMAP.md`, …) | ~0.7 MB | **Unused.** Zero references. |
| `public/libraries/swiper/**` | ~157 KB | **Unused on public pages.** Loaded only on admin pages, where no swiper markup exists. |
| `public/libraries/jquery/jquery-3.7.0.min.js` | ~87 KB | **Unused on public pages** (nothing on public pages uses jQuery). |
| `public/frontend/jquery/jquery-3.6.4.min.js` | ~90 KB | **Unused entirely.** |
| `public/frontend/libraries/bootstrap/**` | ~230 KB | Used by public pages only for a handful of grid/utility classes. Replaced by Tailwind. |
| `public/frontend/libraries/fontawesome/**` (~2 000 SVG files) | ~13 MB | Used only for 6 brand icons + 1 copyright icon. Replaced by inline SVG components. |
| `public/frontend/style/**` (`.css` + `.css.map` + `.scss`) | ~91 KB | Replaced by Tailwind. |
| `public/frontend/img/skill-logo/*` | ~1.3 MB | Logos for the marquee. Replaced by name badges from the `skills` table. |
| `public/script/bootstrap*.js` (7 files + 7 maps) | ~2.4 MB | Public pages use `main.js` only; admin uses it for nothing (delete flow uses SweetAlert2). |
| `public/libraries/.DS_Store` | 6 KB | macOS artefact. |

**Total removable from the public side: ~32 MB across ~2 200 files.**

### 8.2 Must be retained for admin compatibility

`public/libraries/jquery/jquery-3.7.0.min.js` (admin delete flow), `public/libraries/toastr/*` (flash messages),
SweetAlert2 via CDN (delete confirmation), `public/frontend/style/navigation/navigation.css` (admin `.logo`),
`public/script/admin.js` (delete flow). Breeze components (`x-app-layout`, `x-input-label`, `x-text-input`,
`x-row`, `x-col`, `x-primary-button`, `x-back-button`, `x-danger-button`, `x-dropdown`, `x-modal`, …) are all in use.

### 8.3 PHP dependencies

`guzzlehttp/guzzle` is required but **never used** in `app/` or `routes/`.
`laravel/sanctum` is applied to `User` but `routes/api.php` is empty.
Both are harmless; neither is removed in Portfolio V2 (see §13).

---

## 9. Technical debt

1. **`DashboardController` is a 4-resource monolith** with `*Skill` / `*Project` / `*Certificate` method suffixes.
2. **Zero FormRequests** except `ListViewRequest` and `ProfileUpdateRequest`; all validation is inline in controllers.
3. **`AppUtility::listView()` is a 220-line generic query builder** with 4 levels of nested relationship search,
   a hand-rolled pagination object, and `$query->count()` executed **twice**. Its results are discarded by every
   admin index view. It also builds SQL by string concatenation of column names from request input
   (`$request['order']` → `orderBy()`) — the values are validated only by `ListViewRequest`'s `min:0`, which
   **does not restrict them to real column names**.
4. **`resources/views/components/list-view.blade.php` is broken**: it tests `count($list)` but `$list` is never
   passed by any caller. It is also never used.
5. **`resources/views/components/navbar.blade.php` is copied from an unrelated project** — it is a Bootstrap
   navbar linking to `/product/haji`, `/product/umroh`, `galeri/foto`, `/#contact-form`, in Indonesian
   (*Beranda*, *Galeri*, *Kontak*), with a logo at `/img/logo.webp` that does not exist. **Zero references.**
6. **Two navbars, two footers, two layouts** for the same site (`partials/navbar` + `partials/footer` for public,
   `layouts/navigation` for admin) with duplicated link lists and duplicated active-route logic.
7. **`app/Models/Post_.php`** — lowercase class, wrong filename, lorem-ipsum payloads, PSR-4 violation.
8. **Duplicated query-then-`all()` pattern** in all three admin index actions.
9. **`greeting()`** hardcodes `Asia/Jakarta` and mutates global state from a view helper.
10. **Mixed route-name semantics**: `/projects` is named `project`, `/certificates` is named `certificate`.
11. **No factory/seeder** for `Project`, `Skill`, `Certificate` — only `UserFactory`.
12. **`public/favicon.ico` is 0 bytes.**
13. **`.styleci.yml` and `.editorconfig`** exist but Pint is never invoked in CI.

---

## 10. Security concerns

| # | Severity | Issue | Portfolio V2 action |
|---|----------|-------|---------------------|
| S1 | **Critical** | `/register` is public (`routes/auth.php`). Anyone can register and then reach `/dashboard`, `/project`, `/skill`, `/certificate` — full read/write/delete on portfolio content. | **Disable public registration** (keep the route + view, but move it behind an env flag / remove from the guest group). Do not delete the auth scaffolding. |
| S2 | **High** | No authorization on admin routes beyond `auth`. Any authenticated account is an admin. | Admin routes stay `auth`-gated; add an `IsAdmin`-style gate based on a configurable allow-list of e-mail addresses, defaulting to permissive so no legitimate lockout can occur. |
| S3 | **High** | Google Apps Script feedback endpoint is an unauthenticated open relay (no CSRF token, no origin check, no rate limit, no captcha). It can be used to write arbitrary rows into the owner's spreadsheet. | Replace with a **server-side** `POST /contact` + `contact_messages` table, validated, honeypot-protected, rate-limited. No third-party credentials required. |
| S4 | **Medium** | `layouts/app.blade.php` interpolates flash messages straight into JS: `toastr['{{ $message[0] }}']('{{ $message[1] }}')`. Any admin-controlled text in a flash message becomes script execution. | Render flash/validation messages as escaped HTML (`{{ }}`), not JS string literals. |
| S5 | **Medium** | `AppUtility::listView()` passes `order`/`dir` from the request into `orderBy()` after only `min:0` validation → invalid-column errors and potential information disclosure via SQL errors. | Constrain `order`/`dir` to a server-side allow-list. |
| S6 | **Medium** | Image uploads: `required|image|mimes:jpeg,png,jpg,gif,webp` with **no `max:` size cap** and no dimension cap. | Add `max:2048` (2 MB) plus MIME/size/dimension validation. |
| S7 | **Low** | `Home/post.blade.php` renders `{!! $post->body !!}` — raw unescaped HTML. Currently unreachable (no route), but the view is retained in the repo. | Delete the dead views along with `Post_.php`. |
| S8 | **Low** | `SESSION_SECURE_COOKIE` is unset in `.env.example`, so on HTTPS the session cookie is still transmitted over plain HTTP. | Document in README; do not force, to avoid breaking local HTTP development. |
| S9 | **Low** | `TrustProxies` is enabled but `TrustHosts` is commented out in `Http/Kernel.php`. | Left as-is (framework default); documented. |
| S10 | **Info** | `.env.example` ships `DB_DATABASE=aplikasiwpu` — a name from an unrelated project. | Change to a neutral default; real credentials are not in the repo. |

**Verified clean:** `.env` is gitignored and not committed. No credentials, API keys, or tokens exist in the
repository. `composer.lock`/`package-lock.json` are committed. CSRF protection is enabled on all `web` routes.
Password hashing uses the framework's `hashed` cast (bcrypt). `password_reset_tokens` present.

---

## 11. Contact / identity data found in the repository

Values below were read directly from the existing source (not guessed):

| Channel | Value | Source |
|---------|-------|--------|
| GitHub | `https://github.com/satriaranggaj` | `Home/contact.blade.php`, `partials/footer.blade.php` |
| Instagram | `https://www.instagram.com/satria_rangga_j` | `Home/contact.blade.php`, `partials/footer.blade.php` |
| WhatsApp | `+62 881-5695-295` → `628815695295` | `Home/contact.blade.php` |
| Facebook | `https://web.facebook.com/satriaranggajati.jati` | `partials/footer.blade.php` |
| Website | `https://satriarangga.my.id` | repository name / domain |
| LinkedIn | `https://www.linkedin.com/in/satriaranggamyid/` | `Home/contact.blade.php`, `partials/footer.blade.php` |
| Résumé | Google Drive PDF `1TIWRqOEkxd1mdV1vQfNSW1S9P5X5djHm` | `partials/navbar.blade.php`, `partials/footer.blade.php` |

**LinkedIn handling:** the brief says *"Do not guess LinkedIn URL. Keep it only if it is verified from existing
configuration/data; otherwise make it configurable and do not prominently display an unverified URL."*
The URL **is** present in the repository source and the most recent commit on `main` is literally
`07cacfb renewed linkedin`. It is therefore treated as *present in existing data* — but it is moved into
`config/portfolio.php` as a **nullable, disabled-by-default** entry so it is never rendered unless the owner
explicitly enables it. It is never auto-generated or guessed.

**Facebook and the résumé link** are removed from the design (out of scope for the specified information
architecture) but their URLs are preserved in `config/portfolio.php` as disabled entries.

---

## 12. Performance issues

| # | Issue | Impact |
|---|-------|--------|
| P1 | 3 × cross-origin `<iframe>` on `/` and `/projects` | Massive LCP/TBT hit; each frame runs a full third-party app; many sites send `X-Frame-Options: DENY`, rendering blank boxes. |
| P2 | 27.3 MB of vendored frontend libraries shipped in the repository | Deployment size, and every legacy `<link>`/`<script>` is a render-blocking request. |
| P3 | `public/frontend/img/photo-profil/IMG_1666.jpg` = **5.93 MB** | Largest Contentful Paint on `/about`. |
| P4 | `public/frontend/img/icon/srj-icon.png` = 103 KB, `git-logo.png` = 285 KB, `jquery-logo.png` = 330 KB | Unoptimised PNGs with no `width`/`height`, no `loading="lazy"`. |
| P5 | Google Fonts: 2 families × 10 weights, `display=swap`, `preconnect` ×2 | ~10 blocking font requests, ~100–200 KB, FOUT/CLS. `Caveat` is used once. |
| P6 | `Project::all()` → PHP `reverse()` → `take(3)` | Full table read + full materialisation to show 3 rows. Same in `Post::lastPost()`. |
| P7 | Admin index pages run 2 queries per resource (`listView` + `::all()`) | 6 queries where 1–2 suffice. |
| P8 | `window.onscroll` re-registers a `scroll` listener **inside** the handler | Unbounded listener growth; jank on scroll. |
| P9 | No `loading="lazy"` / `decoding="async"` / `width`/`height` on any `<img>` | Layout shift on every image. |
| P10 | jQuery loaded on every public page for **zero** public-page usage | ~87 KB of parse-blocking JS. |
| P11 | `Welcome.blade.php` (28 KB of Laravel marketing HTML) and `Home/post*.blade.php` compiled on route cache | Not on any route, but they bloat the view cache. |

---

## 13. Version strategy (decision)

**Portfolio V2 does NOT upgrade Laravel, PHP, or any dependency.**

- Laravel stays at `^10.10`; PHP requirement stays at `^8.1`.
- Tailwind stays at `^3.1`; Vite at `^4`; Alpine at `^3.4`.
- Rationale: UI changes and framework changes in one branch make regressions unbisectable. Portfolio V2 must be
  provably safe on its own before any upgrade is even considered.

Post-V2 recommendation (**documentation only, no action taken**): a separate upgrade branch should evaluate
Laravel 11 → 12, PHP 8.1 → 8.3, Vite 5/6, Tailwind 4, and removal of `guzzlehttp/guzzle` + `laravel/sanctum`
if the API surface stays empty.

---

## 14. Proposed architecture (Portfolio V2)

### 14.1 Frontend

Unify on the stack the admin already uses.

- **Blade + Tailwind CSS 3 + Alpine.js 3 + Vite 4.**
- Bootstrap, jQuery, Font Awesome and hand-written SCSS removed **from the public site only**.
- The admin dashboard keeps its Breeze/Tailwind/jQuery/SweetAlert2/Toastr stack untouched.
- One design system in `tailwind.config.js`: near-black surfaces, off-white text, **one** restrained accent,
  `font-sans` = Inter (self-referenced via `@font-face` from a single variable-font file, with a
  `system-ui` fallback so **zero third-party font requests**).
- Motion limited to reveal/hover/border transitions, wrapped in a Tailwind `motion-safe:` variant and a
  `@media (prefers-reduced-motion: reduce)` block.

### 14.2 Blade architecture

```
resources/views/
  layouts/
    portfolio.blade.php              # public shell: SEO head, theme bootstrap, nav, slot, footer
  components/
    portfolio/
      navbar.blade.php
      footer.blade.php
      section-heading.blade.php
      page-header.blade.php
      project-card.blade.php
      project-thumbnail.blade.php    # real image OR designed 16:9 fallback placeholder
      skill-badge.blade.php
      button.blade.php
      social-link.blade.php
      contact-method.blade.php
      certificate-card.blade.php
      timeline-item.blade.php
      capability-card.blade.php
      icon.blade.php                 # single source of truth for inline SVG icons
      seo.blade.php                  # title / description / canonical / OG / Twitter / JSON-LD
      empty-state.blade.php
  portfolio/
    home.blade.php
    about.blade.php
    contact.blade.php
    certificates/index.blade.php
    projects/index.blade.php
    projects/show.blade.php
```

Admin views are **not** moved or renamed — `Admin/*`, `layouts/app`, `layouts/guest`, `layouts/navigation`
and all Breeze components stay exactly where they are.

### 14.3 Controllers

```
app/Http/Controllers/
  HomeController.php                 # / and /about only
  ProjectController.php             # public /projects and /projects/{project:slug}
  CertificateController.php         # public /certificates
  ContactController.php             # public /contact + POST /contact
  Admin/
    DashboardController.php          # dashboard home only
    SkillController.php              # resource controller
    ProjectController.php            # resource controller
    CertificateController.php        # resource controller
```

All 18 existing admin route **names are preserved verbatim**, so no Blade reference breaks.

### 14.4 Data model (additive only)

`projects` gains: `slug`, `short_description`, `description`, `thumbnail`, `project_type`,
`tech_stack` (json), `live_url`, `github_url`, `featured` (bool), `status`, `sort_order`,
`problem`, `solution`, `highlights` (json), `challenges`, `outcome`, `role`, `year`, `screenshots` (json).

`certificates` gains: `issuer`, `issued_at`, `image`, `description`.
`skills` gains: `name`, `category`, `url`, `sort_order`.
New table: `contact_messages` (`name`, `email`, `subject`, `message`, `ip`, `user_agent`, timestamps).

Backward compatibility:
- `projects.link` is **kept**. `live_url ?? link` is the read path (`Project::liveUrl()` accessor).
- `skills.image` is **kept**; rows without a `name` fall back to a name derived from the file basename.
- `certificates.link` is **kept** as the credential URL fallback.
- New columns are all nullable or defaulted.
- Backfill of `slug` and `live_url` runs inside an idempotent migration (`WHERE ... IS NULL`) so existing rows
  keep working the moment the migration finishes. Nothing is overwritten.

### 14.5 Configuration

`config/portfolio.php` holds all identity + copy that is not in the database: name, role, tagline,
description, contact channels (with optional channels disabled), the "what I build" capability list,
the tech-stack groups, and the development-journey timeline. Content that requires dates (timeline) lives
in config and is **left empty** rather than invented — no fabricated employment history.

### 14.6 SEO

- `components/portfolio/seo.blade.php` renders `<title>`, meta description, canonical, robots,
  Open Graph (5 tags) and Twitter card (4 tags) from `@php` variables, with a project thumbnail as the
  `og:image` for case studies and a site-wide fallback.
- JSON-LD: `Person` + `WebSite` on all public pages, `SoftwareSourceCode`/`CreativeWork` on case studies —
  containing **only** real values.
- `public/robots.txt` rewritten to allow public pages and **disallow** `/dashboard`, `/project`, `/skill`,
  `/certificate`, `/profile`, `/login`, `/register`.
- `GET /sitemap.xml` — dynamic route emitting home, about, projects, certificates, contact, and every
  published project case study.
- Favicon: a real `public/favicon.svg` replaces the 0-byte `.ico`.

---

## 15. Dead code classification

### 15.1 Confirmed dead (verified: zero references, zero routes)

| Path | Reason |
|------|--------|
| `app/Models/Post_.php` | class `post`, PSR-4 violation, no references |
| `resources/views/components/navbar.blade.php` | Bootstrap navbar for an unrelated Hajj/Umrah site; no references |
| `resources/views/Admin/posts/index.blade.php` | no route, no live controller method |
| `resources/views/Admin/posts/form.blade.php` | no route, no live controller method |
| `resources/views/Admin/posts/**` (whole dir) | as above |
| `resources/views/Home/post.blade.php` | no route |
| `resources/views/Home/posts.blade.php` | no route |
| `resources/views/components/list-view.blade.php` | broken (`$list` undefined), no references |
| `resources/views/welcome.blade.php` | Laravel default, no route |
| `public/libraries/ckeditor/**` | no references |
| `public/libraries/lightbox2/**` | no references |
| `public/libraries/swiper/**` | no markup uses it |
| `public/frontend/jquery/**` | no references |
| `public/script/bootstrap*.js` + `.map` | admin's delete flow uses SweetAlert2, not Bootstrap JS |
| `public/libraries/.DS_Store` | macOS artefact |
| `DashboardController` post-CRUD block (90 commented lines) | fully commented out |

### 15.2 Potential dead (deleted only after a final reference sweep)

| Path | Reason |
|------|--------|
| `public/frontend/libraries/bootstrap/**` | public-only; replaced by Tailwind |
| `public/frontend/libraries/fontawesome/**` | public-only; replaced by inline SVG components |
| `public/frontend/style/**` (`.scss`, `.css`, `.css.map`) | replaced by Tailwind |
| `public/frontend/img/skill-logo/*` | logos replaced by name badges |
| `public/frontend/img/project/my-portfolio.png`, `portfolio.png`, `not-found*.png` | blog-era assets |
| `public/libraries/jquery/jquery-3.7.0.min.js` | admin still imports it → **RETAINED** |
| `resources/views/partials/navbar.blade.php`, `partials/footer.blade.php` | replaced by components |
| `resources/views/layouts/main.blade.php` | replaced by `layouts/portfolio.blade.php` |
| `resources/views/Home/**` | replaced by `resources/views/portfolio/**` |

### 15.3 Retained for compatibility

| Path | Reason |
|------|--------|
| `app/Models/Post.php`, `app/Models/Category.php` | tables `posts`/`categories` still exist in production; no data is destroyed |
| `database/migrations/*_create_posts_table.php`, `*_create_categories_table.php` | historical migrations must never be edited or removed |
| `public/libraries/jquery/jquery-3.7.0.min.js` | admin delete-confirmation flow |
| `public/libraries/toastr/*` | admin flash messages |
| `public/script/admin.js` | admin delete-confirmation flow |
| `public/frontend/style/navigation/navigation.css` | admin `.logo` styling |
| `public/frontend/img/icon/srj-icon.png` | favicon + `og:image` fallback |
| `resources/views/Admin/auth/*`, `app/Http/Controllers/Auth/*` | working auth must not be removed |
| All Breeze components (`x-app-layout`, `x-row`, `x-col`, `x-modal`, …) | in use by admin |
| `guzzlehttp/guzzle`, `laravel/sanctum` | unused, but removing them is a dependency change out of scope for V2 |
| `AppUtility::listView()` | used by all three admin index actions — kept, hardened, not rewritten |

---

## 16. Migration strategy

Phased, additive, reversible, and each phase independently deployable.

**Phase 0 — Audit** (this document).

**Phase 1 — Infrastructure (no behaviour change)**
Enable in-memory SQLite for tests so no real database is ever touched by CI or local test runs.
Document the baseline test result.

**Phase 2 — Schema (additive)**
1. `add_portfolio_fields_to_projects_table`
2. `add_portfolio_fields_to_certificates_table`
3. `add_portfolio_fields_to_skills_table`
4. `create_contact_messages_table`
5. `backfill_project_slugs_and_live_urls` — idempotent, `WHERE ... IS NULL`, unique-slug generation.
No `down()` in this phase drops anything that existed before Portfolio V2; new columns are dropped on rollback,
original columns are untouched.

**Phase 3 — Domain layer**
Models (casts, scopes, accessors, `liveUrl()` fallback), `config/portfolio.php`, FormRequests,
`Admin\*` controllers, public `ProjectController`/`CertificateController`/`ContactController`,
cleaned `HomeController`.

**Phase 4 — Presentation**
`layouts/portfolio`, `components/portfolio/*`, the six public views, new `resources/css/portfolio.css`,
`resources/js/portfolio.js`, Tailwind theme extension, favicon, `robots.txt`, `/sitemap.xml`.

**Phase 5 — Cleanup**
Delete the confirmed-dead list, then a full reference sweep before removing each "potential dead" item.
Legacy asset deletion is a separate commit from any Blade change.

**Phase 6 — Verification**
Feature tests for all five public routes, valid/invalid case-study slugs, admin CRUD, and contact submission.
Full suite green (or, at minimum, no regression against the recorded baseline). `npm run build` clean.

**Rollback:** every migration is additive and reversible; the previous Blade/controller code is one `git revert`
away because no commit mixes a schema change with a UI change.

---

## 17. Baseline test result (recorded before any change)

With in-memory SQLite enabled and `npm run build` completed:

```
Tests: 5 failed, 20 passed (60 assertions)
```

Pre-existing failures, all unrelated to Portfolio V2 and all caused by real bugs:

| Test | Cause |
|------|-------|
| `Auth\EmailVerificationTest > email verification screen can be rendered` | `View [auth.verify-email] not found` (finding #2) |
| `Auth\PasswordConfirmationTest > confirm password screen can be rendered` | `View [auth.confirm-password] not found` (finding #2) |
| `Auth\PasswordResetTest > reset password link screen can be rendered` | `View [auth.forgot-password] not found` (finding #2) |
| `Auth\PasswordResetTest > reset password screen can be rendered` | `View [auth.reset-password] not found` (finding #2) |
| `Feature\ExampleTest > the application returns a successful response` | `no such table: skills` — `ExampleTest` does not use `RefreshDatabase` and hit `/`, which queries `skills` |

Portfolio V2 does **not** delete tests. The four view-not-found failures are **fixed** as part of this work.
The `ExampleTest` failure is fixed by adding `RefreshDatabase` to it (the test was never valid without a database).

Both are now green: see §19.6.

---

## 18. Audit limitations (declared honestly)

- The **production database was not accessed.** `DB_DATABASE=aplikasiwpu` on the local MySQL server was
  deliberately left untouched, so the number of existing `projects` / `skills` / `certificates` rows is unknown.
  Every migration is therefore written to be safe for **0 rows and for 10 000 rows**.
- Existing production **project `link` values are unknown**, so `live_url ?? link` is the guaranteed-safe read
  path and the backfill never overwrites an existing value.
- No **project was added to the database** as part of this work. The strongest projects (Lensku / BarcodeIdentify,
  Undangly, Davina Event) are described as *staged seed content in the README*, not inserted as data, because
  inventing URLs, years, metrics or client names is explicitly out of bounds.
- The Google Apps Script endpoint was **not** probed. Its current liveness is unverified; it is replaced by a
  server-side implementation that requires no third-party credentials.

---

## 19. What Portfolio V2 actually changed

Everything below was implemented on `feat/portfolio-v2`. No framework or dependency was upgraded.

### 19.1 Bugs found by this audit and fixed

| Audit finding | Fix |
|---------------|-----|
| #1 — public `/register` granted dashboard access to any visitor | `RegisteredUserController` returns 404 unless `portfolio.allow_registration` is true; `ALLOW_REGISTRATION` env key added, default `false`. Route and view kept intact. |
| #2 — four auth pages were HTTP 500 (`View [auth.*] not found`) | All four controllers now point at `Admin.auth.*`, where the views actually live. **This is what fixed 4 of the 5 pre-existing test failures.** |
| #3 — Google Sheets feedback endpoint was an open, unvalidated relay | Replaced with `POST /contact` → `contact_messages`, validated by `StoreContactMessageRequest`, honeypot-protected, rate limited (`throttle:contact`, 5/min/IP), with a graceful server-error path. No third-party credentials required. |
| #4 — `public/script/main.js` threw a `TypeError` on every admin page | Removed from the admin layout and deleted from the repository. |
| #5 — `Admin/projects/form.blade.php` referenced a non-existent `#image` element | Form rewritten; the broken inline script is gone and the new preview uses a valid selector. |
| S4 — flash messages interpolated raw text into JavaScript | `layouts/app.blade.php` now uses `@json()` + an allow-list of Toastr methods. |
| S5 — `AppUtility::listView()` passed unvalidated `order`/`dir` into `orderBy()` | The utility and `ListViewRequest` were deleted once verified unreferenced, which removes the injection surface entirely. |
| #12 — `Post_.php` PSR-4 violation | Deleted. `composer install` no longer prints the autoload warning. |
| #15 — CI triggered on `master`, repo uses `main` | Workflow now targets `main` and `feat/**`, adds a Pint style job and a `npm run build` step. |
| — | Admin mobile navigation only contained "Dashboard" | All admin sections are now reachable on mobile. |
| — | `public/favicon.ico` was 0 bytes | Real `public/favicon.svg` added and referenced. |
| — | `AppUtility::listView()` result was built and thrown away on all three index pages | Index actions now run one query. |
| — | `Project::rev()` / `lastProject()` / `Post::rev()` / `lastPost()` loaded whole tables | Replaced with `published()`, `featured()`, `ordered()` scopes. |
| — | Skill logos read from `storage/` while `.env.example` shipped `FILESYSTEM_DISK=local` | Documented in `.env.example` and the README (`php artisan storage:link`). |

### 19.2 New bugs caught by the new tests during implementation

Both were real defects in the first draft of the admin controller and are now covered by
`tests/Feature/Admin/ProjectManagementTest.php`:

1. **Slug regeneration broke published URLs.** Updating a project without submitting the slug
   regenerated it from the title, so `/projects/lensku` silently became `/projects/sit-explicabo-iste`.
   The slug is now only touched when the form actually submits it (or when it is empty).
2. **An empty update cleared the legacy `link` column.** `link` is now only written when the form
   submits it, so the `live_url ?? link` fallback can never be destroyed by an unrelated edit.

### 19.3 Schema changes (all additive)

| Migration | Effect | Reversible |
|-----------|--------|------------|
| `2026_10_04_100000_add_portfolio_fields_to_projects_table` | +19 nullable columns and a unique `slug` | Yes — drops only the new columns |
| `2026_10_04_100100_add_portfolio_fields_to_skills_table` | +`name`, `category`, `url`, `sort_order` | Yes |
| `2026_10_04_100200_add_portfolio_fields_to_certificates_table` | +`issuer`, `issued_at`, `image`, `description`, `sort_order` | Yes |
| `2026_10_04_100300_create_contact_messages_table` | New table | Yes |
| `2026_10_04_100400_backfill_portfolio_slugs_and_names` | Fills only **empty** `projects.slug` / `projects.live_url` / `skills.name`; copies `link` only when it starts with `http` | **No-op by design** — nothing to undo |
| `2026_10_04_100500_relax_legacy_not_null_columns` | Relaxes `NOT NULL` on `projects.link`, `certificates.link`, `skills.image` | Yes |

`2026_10_04_100500` uses driver-aware raw `ALTER TABLE` rather than `$table->change()`, because
`change()` requires `doctrine/dbal` — a new dependency that would have to be installed on the
production server. No new dependency was added. The SQLite branch rebuilds the table, which is what
lets the same migration run under the test suite.

**Nothing was dropped. No production data was read, written or deleted.**

### 19.4 Dead code actually removed

Confirmed dead, verified by a reference sweep before deletion:

`app/Http/Controllers/DashboardController.php`, `app/Utilities/AppUtility.php`,
`app/Http/Requests/ListViewRequest.php`, `app/Models/Post_.php`, `resources/views/Home/**`,
`resources/views/Admin/posts/**`, `resources/views/partials/**`,
`resources/views/layouts/main.blade.php`, `resources/views/components/navbar.blade.php`,
`resources/views/components/list-view.blade.php`, `resources/views/welcome.blade.php`.

Assets: `public/libraries/{ckeditor,lightbox2,swiper}`, `public/libraries/.DS_Store`,
`public/frontend/libraries/{bootstrap,fontawesome}`, `public/frontend/jquery`,
`public/frontend/style/{main,navbar,footer,responsive}`,
`public/frontend/img/{photo-profil,project,skill-logo}`, `public/script/bootstrap*` (14 files),
`public/script/main.js`.

`public/frontend/style/navigation/navigation.css` was **kept** — the admin layouts still load it.

### 19.5 Deliberately retained

| Path | Reason |
|------|--------|
| `app/Models/Post.php`, `app/Models/Category.php` | `posts`/`categories` tables still exist; no data is destroyed |
| All historical migrations | Never edited or removed |
| `public/libraries/jquery/jquery-3.7.0.min.js` | Admin delete-confirmation flow (`admin.js`) |
| `public/libraries/toastr/*` | Admin flash messages |
| `public/script/admin.js` | Admin delete-confirmation flow |
| `public/frontend/style/navigation/navigation.css` | Admin `.logo` styling |
| `public/frontend/img/icon/srj-icon.png` | `apple-touch-icon` and OG fallback |
| `public/favicon.ico` | Browsers request `/favicon.ico` implicitly; a 404 there is noisier than an empty file |
| All Breeze components and `Admin/auth/*` | Working auth and admin UI |
| `guzzlehttp/guzzle`, `laravel/sanctum` | Unused, but removing them is a dependency change out of scope for V2 |

### 19.6 Final verification

```
Test suite:      77 passed (249 assertions)   — baseline was 5 failed, 20 passed
Pint (--test):   112 files PASS
Vite build:      clean, 54.11 kB CSS (9.74 kB gzip) + 79.52 kB JS (29.55 kB gzip)
Public assets:   ~37 MB / ~2 300 files removed
```

Every public route now ships **one** CSS file and **one** JS bundle, down from seven stylesheets and
three scripts including jQuery and Bootstrap. Zero third-party font requests — the previous version
loaded two Google Fonts families at ten weights each.

### 19.7 Still open (deliberately)

- **Laravel / PHP / dependency upgrades.** Documented in §13, not attempted. Portfolio V2 ships on
  the same `laravel/framework ^10.10`, `php ^8.1`, Tailwind `^3.1`, Vite `^4`, Alpine `^3.4` stack
  as before.
- **`Post` / `Category`.** Still present with their tables. They can be migrated away
  deliberately once the owner confirms no rows matter.
- **The 5.93 MB profile photo** was deleted with the About page that referenced it. If a portrait is
  wanted back, it should be resized and stored as a project-quality asset, not dropped into
  `public/`.
- **`AppServiceProvider` has no `TrustHosts` configuration.** Framework default; noted in §10 (S9).
- **`guzzlehttp/guzzle` and `axios`** are both declared but never actually called. They were left in
  place because removing them is a dependency change, which §13 explicitly defers to a later phase.