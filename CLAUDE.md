<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/flux (FLUXUI_FREE) - v2
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `fortify-development` — ACTIVATE when the user works on authentication in Laravel. This includes login, registration, password reset, email verification, two-factor authentication (2FA/TOTP/QR codes/recovery codes), profile updates, password confirmation, or any auth-related routes and controllers. Activate when the user mentions Fortify, auth, authentication, login, register, signup, forgot password, verify email, 2FA, or references app/Actions/Fortify/, CreateNewUser, UpdateUserProfileInformation, FortifyServiceProvider, config/fortify.php, or auth guards. Fortify is the frontend-agnostic authentication backend for Laravel that registers all auth routes and controllers. Also activate when building SPA or headless authentication, customizing login redirects, overriding response contracts like LoginResponse, or configuring login throttling. Do NOT activate for Laravel Passport (OAuth2 API tokens), Socialite (OAuth social login), or non-auth Laravel features.
- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `fluxui-development` — Use this skill for Flux UI development in Livewire applications only. Trigger when working with <flux:*> components, building or customizing Livewire component UIs, creating forms, modals, tables, or other interactive elements. Covers: flux: components (buttons, inputs, modals, forms, tables, date-pickers, kanban, badges, tooltips, etc.), component composition, Tailwind CSS styling, Heroicons/Lucide icon integration, validation patterns, responsive design, and theming. Do not use for non-Livewire frameworks or non-component styling.
- `livewire-development` — Use for any task or question involving Livewire. Activate if user mentions Livewire, wire: directives, or Livewire-specific concepts like wire:model, wire:click, wire:sort, or islands, invoke this skill. Covers building new components, debugging reactivity issues, real-time form validation, drag-and-drop, loading states, migrating from Livewire 3 to 4, converting component formats (SFC/MFC/class-based), and performance optimization. Do not use for non-Livewire reactive UI (React, Vue, Alpine-only, Inertia.js) or standard Laravel forms without Livewire.
- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: test()/it()/expect() syntax, datasets, mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>

# Project Description
This is a single vendor ecommerce application built with Laravel 13, Livewire 4 (sfc with components and full page components for page), and Tailwind CSS 4. It includes features like user authentication with Laravel Fortify, a dynamic frontend built with Livewire and Flux UI components, and comprehensive testing using Pest PHP. The application follows Laravel best practices and is designed for scalability and maintainability.

**DB Schema:**
- users table (default Laravel users table with Fortify fields), add phone, role (admin,editor,customer) columns
- Categories table migration
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
    $table->dateTime('featured_at')->nullable(); // for model boolean accessor is_featured
    $table->timestamps();
});
```
- Brands table migration
```php
Schema::create('brands', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->dateTime('featured_at')->nullable(); // for model boolean accessor is_featured
    $table->timestamps();
});
```
- Products table migration
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete(); // primary category
    $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->dateTime('featured_at')->nullable(); // for model boolean accessor is_featured
    $table->dateTime('available_at')->nullable(); // is_available accessor
    $table->timestamps();
    // thumbnail and gallery images will be handled by spatie/laravel-medialibrary, so no need for image fields here
});
// many to many product_category pivot table
Schema::create('category_product', function (Blueprint $table) {
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();

    $table->primary(['category_id', 'product_id']);
});
```
- Attribute table migration
```php
Schema::create('attributes', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Size, Color
    $table->timestamps();
});
```
- Attribute Values table migration
```php
Schema::create('attribute_values', function (Blueprint $table) {
    $table->id();
    $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
    $table->string('value'); // M, Red
    $table->timestamps();
});
```
- Product Variants table migration
```php
Schema::create('product_variants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->string('sku')->unique();
    $table->decimal('price', 10, 2);
    $table->decimal('sale_price', 10, 2)->nullable();
    $table->decimal('weight', 8, 2)->nullable();
    $table->timestamps();
});
// variant_attribute_value pivot table
Schema::create('variant_attribute_value', function (Blueprint $table) {
    $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
    $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();

    $table->primary(['variant_id', 'attribute_value_id']);
});
```
- Stocks table migration
```php
Schema::create('stocks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
    $table->integer('quantity')->default(0);
    $table->integer('reserved_quantity')->default(0);
    $table->timestamps();
});
```
- Coupons table migration
```php
Schema::create('coupons', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();

    $table->enum('type', ['fixed', 'percent']);
    $table->decimal('value', 10, 2);

    $table->decimal('min_order_amount', 10, 2)->nullable();
    $table->integer('usage_limit')->nullable();
    $table->integer('used_count')->default(0);

    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});
```
- Addresses table migration
```php
Schema::create('addresses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('type'); // billing/shipping
    $table->string('address_line');
    $table->string('city');
    $table->string('postal_code')->nullable();
    $table->string('country');
    $table->boolean('is_default')->default(false);
    $table->timestamps();
});
```
- Orders table migration
```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    $table->string('order_number')->unique();

    $table->enum('status', ['pending', 'paid', 'shipped', 'completed', 'cancelled'])->default('pending');
    $table->enum('payment_status', ['unpaid', 'paid', 'failed'])->default('unpaid');
    $table->enum('shipping_status', ['pending', 'shipped', 'delivered'])->default('pending');

    $table->decimal('subtotal', 10, 2);
    $table->decimal('shipping_fee', 10, 2)->default(0);
    $table->decimal('discount', 10, 2)->default(0);
    $table->decimal('total', 10, 2);

    $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();

    $table->timestamps();
});
```
- Order Items table migration
```php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();

    $table->string('description')->nullable();

    $table->decimal('price', 10, 2);
    $table->integer('quantity');
    $table->decimal('total', 10, 2);
    
    $table->json('variant_attributes')->nullable();

    $table->timestamps();
});
```
- Shipments table migration
```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();

    $table->string('method'); // e.g. card, bkash, bank, cash etc.
    $table->decimal('amount', 10, 2);

    $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
    $table->string('transaction_id')->nullable();
    $table->timestamp('paid_at')->nullable();

    $table->timestamps();
});
```

- Coupon usages table migration (to track which user used which coupon in which order, for enforcing usage limits and showing usage history)
```php
Schema::create('coupon_usages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->timestamp('used_at')->nullable();
    $table->timestamps();
});
```
- Carts table migration
```php
Schema::create('carts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('session_id')->nullable();
    
    $table->string('coupon_code')->nullable();
    $table->decimal('subtotal', 10, 2)->default(0);
    $table->decimal('discount', 10, 2)->default(0);
    $table->decimal('total', 10, 2)->default(0);
    
    $table->json('meta')->nullable(); // for storing any additional data shipping, billing, discount
    $table->timestamps();
});
```
- Cart Items table migration
```php
Schema::create('cart_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
    $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();    
    
    $table->string('description')->nullable();
    $table->integer('quantity');
    $table->decimal('price', 10, 2);
    $table->decimal('total', 10, 2)->default(0);
    $table->timestamps();
});
```
- Reviews table migration
```php
Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    $table->tinyInteger('rating'); // 1-5
    $table->text('comment')->nullable();

    $table->timestamps();
});
```
- Banners table migration
```php
Schema::create('banners', function (Blueprint $table) {
    $table->id();
    $table->string('category'); // e.g. home, sidebar, product, etc.
    $table->string('title');
    $table->string('slug')->unique();
    $table->string('link')->nullable();
    $table->text('description')->nullable();
    $table->string('position')->nullable(); // for banner positioning and ordering in the frontend. e.g. home_top, home_middle, home_bottom, sidebar_top, sidebar_bottom, etc.
    $table->dateTime('featured_at')->nullable(); // for model boolean accessor is_featured
    $table->timestamps();
    // image will be handled by spatie/laravel-medialibrary, so no need for image fields here
});
```
- Media table (for spatie/laravel-medialibrary): publish the migration


**Relationships:**
- User: hasMany Orders, Addresses, Reviews, CouponUsages
- Category: hasMany Products, belongsTo parent Category, hasMany child Categories
- Brand: hasMany Products
- Product: belongsTo Category and Brand, hasMany Variants and Reviews, belongsToMany Categories (via `category_product`)
- ProductVariant: belongsTo Product, hasOne Stock, belongsToMany AttributeValues (via `variant_attribute_value`)
- Attribute: hasMany AttributeValues
- AttributeValue: belongsTo Attribute, belongsToMany ProductVariants (via `variant_attribute_value`)
- Order: belongsTo User, hasMany OrderItems, hasOne Payment, belongsTo Coupon
- OrderItem: belongsTo Order and ProductVariant
- Payment: belongsTo Order
- Coupon: hasMany Orders and CouponUsages
- Cart: belongsTo User, hasMany CartItems, belongsTo Coupon (via `coupon_code`)
- CartItem: belongsTo Cart and ProductVariant
- CouponUsage: belongsTo Coupon, User, and Order
- Address: belongsTo User
- Review: belongsTo Product and User
- Shipment: belongsTo Order
- Stock: belongsTo ProductVariant

**External Packages:**
- Use spatie/laravel-medialibrary for media management (product images, etc.)
- Use staudenmeir/laravel-adjacency-list for category parent-child relationships
- Use cviebrock/eloquent-sluggable for generating slugs from names (products, categories, brands)
- use wildside/userstamps for adding `$table->userstamps()` to tables where tracking which user created/updated records is useful (e.g. products, orders, coupons, etc.). add this column to the relevant tables and use the `Userstamps` trait in the corresponding models.

**Coding Standards / Guidelines:**
- Follow laravel 13 best practices for controllers, models, migrations, factories, seeders, and tests.
- Try to follow using laravel attributes whenever possible, for example in models, form requests, and policies.
- Use Livewire 4 single file components for all frontend interactivity, including product listing, filtering, cart management,
- For pages use livewire full page components, for smaller interactive elements use livewire sfc with flux ui components.
- For complex or reusable logic, create service classes in the `app/Services` directory.
- Create authorization policies where appropriate, and use them in controllers and Livewire components.
- For transactional operations (like placing an order), use database transactions to ensure data integrity.
- For concurrency-sensitive operations (like stock management), use row-level locking to prevent race conditions. (i.e Coupon must be locked Otherwise usage limit bypass possible)
- Write comprehensive tests for all endpoints (at least all get endpoints and critical business logic) using Pest PHP. Use factories to create test data and cover edge cases.
- Use Tailwind CSS 4 for all styling, and follow existing design patterns in the application. Use the `tailwindcss-development` skill for any Tailwind-related work.
- Use Laravel's built-in features (like Eloquent relationships, query scopes, accessors/mutators, form requests, policies, etc.) to keep code clean and maintainable.

**Features/Business logic/Data Flow:**
- use laravel livewire starter kit given built in dashboard for admin pages (mount on /admin)
- use role `admin` for admin panel authorization
- admin panel features:
    - product management (CRUD, image uploads with medialibrary, variant management, stock management)
    - category and brand management
    - order management (view orders, update status, manage shipments)
    - coupon management
    - banner management
    - user management (view users, manage roles)
- public features:
    - product listing with filtering by category, brand, attributes, and search
    - product detail pages with reviews
    - shopping cart with coupon code application
    - checkout process with address management and payment (simulate payment for this project)
    - direct product orders without cart (buy now button)
    - cart checkout with updated cart calculations during interaction and coupon application 
    - order placement with transactional integrity and stock management. NEVER reduce stock immediately, reserved_quantity first, finalize after payment
    - for payment just use a simple form to simulate payment and update order/payment status accordingly, no need to integrate real payment gateway for this project
    - order history and details for customers (users) with a simple customer dashboard for customer management of orders, addresses, and reviews
    - product reviews with rating and comment

- Home Page:
    - Add a banner slider (multiple banners with category "home" and position "hero"), add others single banner (`featured_at` filter with `home` category) in different sections (e.g. top, middle, bottom etc.) 
    - New arrivals (latest products by created_at and only available)
    - Featured products (products with `featured_at` and only available)
    - latest featured category and brand products (with `featured_at` filter in latest order)
    - Nabvar has Search bar for products, featured categories, and links to cart and customer dashboard / (login/register)
    - Clicking cart shows a dropdown with cart items and total, and a link to the cart page
    - Footer with links to featured categories, brands, and other pages (about us, contact, etc.)
- Product Listing Page (/products or /category/{slug} or /brand/{slug}):
    - List products with pagination
    - Filters for category, brand, attributes (e.g. size, color), price range, and search
    - Sorting options (newest, price low to high, price high to low)
- Product Detail Page (/product/{slug}):
    - Show product details, images, price, variants, and reviews
    - Allow selecting variants (e.g. size/color) and adding to cart or buying directly
    - Show related products based on the same category or brand