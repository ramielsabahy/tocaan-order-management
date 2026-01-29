# Tocaan Order Management

A Laravel-based API for order management with authentication, order lifecycle handling, and payment integration (Credit Card and PayPal).

## Features

- **Authentication** — Register and login via Laravel Sanctum (API tokens)
- **Orders** — Create, read, update, and delete orders with order items
- **Order confirmation** — Confirm orders to move them to a confirmed state
- **Payments** — Process payments for orders via Credit Card or PayPal. Uses the **Strategy pattern** so new payment methods can be added with minimal code changes.
- **Products** — Product catalog used in orders

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL 5.7+ (or MariaDB equivalent)

## Getting Started

### 1. Clone the repository

```bash
git clone <https://github.com/ramielsabahy/tocaan-order-management>
cd TocaanOrderManagement
```

### 2. Install dependencies

```bash
composer install
```

### 3. Environment setup

Copy the example environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure the database

Edit `.env` and set your MySQL credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tocaanordermanagement
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Create the database (e.g. `tocaanordermanagement`) in MySQL if it does not exist.

### 5. Run migrations

```bash
php artisan migrate
```

### 6. (Optional) Payment gateways

For payments to work, configure in `.env`:

- **Credit Card:** `CREDIT_CARD_API_KEY`, `CREDIT_CARD_API_SECRET`
- **PayPal:** `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, `PAYPAL_MODE` (e.g. `sandbox` or `live`)

### 7. Start the development server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

### Quick setup (all-in-one)

You can use the built-in setup script to install dependencies, copy `.env`, generate the key, run migrations, and build assets:

```bash
composer run setup
```

Then configure `.env` (especially database and payment settings) and run:

```bash
php artisan serve
```

## API Documentation

API endpoints, request/response examples, and usage are documented in Postman:

**[Order Management API — Postman Documentation](https://documenter.getpostman.com/view/3208343/2sBXVo8nqE)**

Use this collection to explore and test the authentication, orders, confirmation, and payment endpoints.

## Extending payment gateways

The payment layer uses the **Strategy pattern**: each gateway is a separate class implementing `App\Contracts\PaymentInterface`. To add a new payment method:

1. **Create a gateway service**  
   Add a new class in `app/Services/` (e.g. `StripeService.php`) that implements `PaymentInterface`:
   - `processPayment(Payment $payment): array` — call the external API and return `['success' => bool, 'transaction_id' => string, 'message' => string, 'gateway_response' => mixed]`.
   - `validateCredentials(): bool` — return whether the gateway config (API keys, etc.) is present and valid.

2. **Register the gateway in the factory**  
   In `app/Services/PaymentGatewayFactory.php`:
   - Add a new case in the `create(string $method)` match (e.g. `'stripe' => new StripeService()`).
   - Add the method key to the array returned by `getSupportedMethods()` (e.g. `'stripe'`).

3. **Add configuration**  
   In `config/payment.php`, add an entry under `gateways` with the keys your service needs (e.g. `api_key`, `api_secret`), using `env()` for secrets. Add the corresponding variables to `.env` and `.env.example`.

No changes are required in controllers, actions, or routes — the existing payment endpoint accepts a `payment_method` value and the factory returns the right implementation.

## Running tests

```bash
php artisan test
```

> **Note:** The current test suite does not cover the whole application logic. The tests are included mainly to showcase test-writing skills rather than full coverage.
