# Chapter of You

Laravel 12, Inertia, Vue 3, and TypeScript storefront with an administration area for products, orders, vouchers, journals, and marketplace integrations.

## Local setup

Use PHP 8.4, Composer, and Node 22.22.2 or newer in the Node 22 series to match CI. The frontend test tools also support Node 24.15+ and Node 26+.

```sh
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

Configure a local database and any integration credentials in `.env`, then run:

```sh
php artisan migrate
composer dev
```

`composer dev` starts Laravel, Vite, a queue listener, and the log viewer. Integration credentials are needed for live Stripe, address lookup, marketplace, and generated journal features. Use Stripe test credentials for local checkout testing.

## Checks

```sh
vendor/bin/phpunit
vendor/bin/pint --test
npm run format:check
npm run lint
npm run typecheck
npm run test:frontend
npm run check:conventions
npm run build
```

On a fresh checkout, run `php artisan wayfinder:generate --with-form` before type checking. Vite also regenerates route helpers when building. Generated helpers are not committed.

PHP tests use an isolated in-memory SQLite database. Checkout tests replace Stripe access and fake email delivery. Invoice tests render a real PDF. The test suite does not prove that production credentials or external services are configured correctly.

Frontend interaction tests use Vitest, Vue Test Utils, and jsdom. They exercise upload queues, image visibility, FAQ editing, usage previews, gallery selection, and the product editor submission payload without making network requests.

Use `vendor/bin/pint`, `npm run format`, and `npm run lint:fix` to apply formatting fixes deliberately. CI runs checks without modifying files. Keep comments to one physical line and do not use em dashes or their encoded equivalents.

## Checkout structure

- `CheckoutTotals` calculates prices and shipping using integer pence internally.
- `StripePayments` provides the injectable boundary for Stripe requests.
- `CreateOrder` persists order details and gift vouchers without delivering email.
- `CheckoutController` verifies payment ownership, handles retries, and commits order and voucher changes atomically.
- `OrderNotifications` runs after a successful commit; notification failures are logged without undoing the purchase.

Shipping behaviour remains: the lowest flat courier rate when no item uses per-item shipping, per-item charges otherwise, free shipping from GBP 50 before discounts, and GBP 2.99 additional postage for physical gift vouchers.

## Product components

The product editor keeps its Inertia form and submission in `pages/admin/product/CreateEdit.vue`. `components/admin/product/` owns the image, FAQ, and usage fields through typed models. Shared editor controls use `resources/css/admin-product-fields.css`; each section keeps its own scoped styles.

The storefront gallery and FAQ accordion live in `components/product/`. The product page retains payment/cart actions, reviews, metadata, and the full-screen image viewer.

## Deployment notes

Build assets, install production Composer dependencies, and run pending migrations using the existing deployment process. Keep the queue worker and Laravel scheduler running. Queued order confirmations and admin alerts require a functioning queue worker. Failed notification enqueueing is logged with the order ID; administrators can resend confirmations and gift vouchers from the existing admin pages.

The schema-alignment migration preserves legacy names and product-view history. It is conditional for installations that already use the expected schema and deliberately does not remove preserved data on rollback. Verify it against a staging copy of the production database before deployment.

This refactor has not been deployed. Production database behaviour, real Stripe checkout, email delivery, and third-party integrations still require staging smoke tests.
