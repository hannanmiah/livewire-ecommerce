# Livewire E-Commerce

A single-vendor e-commerce application built with **Laravel 13**, **Livewire 4 (SFC pages/components)**, **Flux UI**, and **Tailwind CSS 4**.

## Features

- Public storefront:
  - Homepage with featured content and banners
  - Product listing with filtering/sorting
  - Product detail pages with variants and reviews
  - Cart and checkout flow with coupon support
- Customer area:
  - Order history and order details
  - Address management
  - Review management
- Admin panel (`/admin`, role protected):
  - Dashboard
  - Category, brand, product, coupon, banner, and user management
  - Order management and status handling
- Auth:
  - Laravel Fortify login/register/password reset/email verification/2FA support

## Tech Stack

- PHP 8.4+
- Laravel 13
- Livewire 4
- Flux UI
- Tailwind CSS 4 + Vite
- Pest (testing)
- Spatie Media Library (media storage)
- `staudenmeir/laravel-adjacency-list` (category tree)
- `cviebrock/eloquent-sluggable` (slug generation)
- `wildside/userstamps` (created_by/updated_by)

## Quick Start

1. Clone and install dependencies:

```bash
composer install
npm install
```

If your environment needs Flux package auth:

```bash
composer config http-basic.composer.fluxui.dev "<FLUX_USERNAME>" "<FLUX_LICENSE_KEY>"
```

2. Set up environment and database:

```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
```

3. Run the app:

```bash
composer dev
```

This starts Laravel, queue listener, logs, and Vite together.

## Default Seeded Accounts

- **Admin**
  - Email: `admin@example.com`
  - Password: `password`
- **Customer**
  - Email: `test@example.com`
  - Password: `password`

## Usage

- Storefront: visit `/`
- Product catalog: `/products`, `/category/{slug}`, `/brand/{slug}`
- Cart: `/cart`
- Checkout: `/checkout` (authenticated + verified)
- Customer dashboard: `/account/orders`, `/account/addresses`, `/account/reviews`
- Admin dashboard: `/admin` (requires `admin` role)

## Useful Commands

```bash
# Full setup
composer setup

# Local development stack
composer dev

# Build frontend assets
npm run build

# Lint (fix)
composer lint

# Lint (check only)
composer lint:check

# Run all tests
composer test

# Run tests directly
php artisan test --compact
```

## Testing & CI

- Test suite uses **Pest** and Livewire component tests.
- CI workflows run linting and tests in GitHub Actions.
- Test matrix currently targets PHP **8.4** and **8.5**.

## Project Structure

- `app/Models` - Eloquent models and domain state
- `app/Services` - Cart, coupon, and order business logic
- `resources/views/pages` - storefront/customer Livewire full-page SFCs
- `resources/views/admin` - admin Livewire full-page SFCs
- `resources/views/components` - reusable Livewire/Blade UI components
- `routes/web.php` - storefront and account routes
- `routes/admin.php` - admin routes (auth + verified + admin middleware)
- `tests/Feature`, `tests/Unit` - Pest test suites

## Notes

- Product/banner images are managed through Media Library (`media` table).
- Coupon application is code-based (`carts.coupon_code`).
- Stock flow is reservation-first, then final quantity deduction on successful payment.
