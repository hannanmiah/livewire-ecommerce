# Copilot instructions for `livewire-ecommerce`

## Build, test, and lint commands
- Initial setup: `composer setup`
- Full local dev stack (Laravel server, queue worker, logs, Vite): `composer dev`
- Frontend dev/build: `npm run dev` / `npm run build`
- Format/fix PHP style: `composer lint`
- Style check only: `composer lint:check`
- Full suite used by contributors: `composer test` (runs lint check + tests)
- Run tests directly: `php artisan test --compact`
- Run one test file: `php artisan test --compact tests/Feature/CartTest.php`
- Run one test by name: `php artisan test --compact --filter="cart can apply valid coupon"`
- CI-style test runner: `./vendor/bin/pest`

## High-level architecture
- This app is Laravel 13 + Fortify auth + Livewire 4 SFCs + Flux UI + Tailwind 4.
- Routing is split across `routes/web.php`, `routes/settings.php`, and `routes/admin.php`. Most pages are mounted via `Route::livewire(...)` using named Livewire namespaces (`pages::...`, `admin::...`).
- Livewire view-based components are the primary UI architecture:
  - Reusable components: `resources/views/components`
  - Storefront pages: `resources/views/pages`
  - Admin pages: `resources/views/admin`
  - Namespace/layout wiring is defined in `config/livewire.php`.
- Storefront and admin are separate layouts (`layouts::app`, `layouts::admin`), with admin access enforced by the custom `admin` middleware alias in `bootstrap/app.php` (`EnsureUserIsAdmin` checks `user()->isAdmin()`).
- Core commerce workflows are service-driven in `app/Services`:
  - `CartService` for guest/session + user carts, coupon application, merge logic.
  - `CouponService` for coupon validation/usage recording with DB locking.
  - `OrderService` for transactional checkout, payment simulation, stock reservation/finalization.
- Product/brand/banner media uses Spatie Media Library collections (not image columns), and category hierarchy uses adjacency-list recursion.

## Key conventions in this repository
- Livewire components are SFC Blade files with inline PHP classes (many files use the `⚡` prefix). Component classes commonly use Livewire attributes such as `#[Title]`, `#[Layout]`, `#[Computed]`, `#[Url]`, and `#[Locked]`.
- Models use PHP attributes for metadata (for example `#[Fillable(...)]`, `#[Scope]`) and keep business helpers in model accessors/scopes.
- Featured/availability flags are timestamp-driven (`featured_at`, `available_at`) rather than booleans.
- Stock handling is two-phase: reserve first (`reserved_quantity`), deduct actual `quantity` after successful payment.
- Cart coupons are linked by code (`carts.coupon_code` -> `coupons.code`), not coupon ID.
- Userstamps are enabled on selected models/tables (`created_by`, `updated_by`) via `wildside/userstamps`.
- UI interaction patterns consistently use Flux components, `wire:navigate`, and `Flux::toast(...)` for feedback.
- Pest is the test framework, with `RefreshDatabase` applied globally in `tests/Pest.php`; feature tests heavily use `Livewire::test(...)` against namespaced component names.

## Existing project assistant configs to align with
- `AGENTS.md`, `CLAUDE.md`, and `GEMINI.md` contain aligned project guidance (Laravel/Livewire/Flux/Tailwind/Pest focus and architecture constraints).
- `opencode.json` enables the Laravel Boost MCP server (`php artisan boost:mcp`), and repository-specific skills exist under `.github/skills/`.
