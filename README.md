# Paxos Laravel Application

A Laravel application for managing lender identities, accounts, and profiles with integration to the Paxos API.

## Features

- **User Authentication** with role-based access control (Admin, Lender, Borrower)
- **Lender Dashboard** with statistics and quick actions
- **Identity Management** - Create and manage lender identities
- **Account Management** - Create accounts linked to identities
- **Profile Management** - Automatically create profiles when creating accounts
- **Paxos API Integration** - Seamless integration with Paxos sandbox API

## Requirements

- PHP **8.2+** (with common extensions: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) + npm (for Vite / frontend assets)

## Local setup and running

### Option A: One-shot setup (recommended)

From the project root:

```bash
composer run setup
```

This runs `composer install`, creates `.env` from `.env.example` if missing, `php artisan key:generate`, `php artisan migrate`, `npm install`, and `npm run build`.

Then configure **Paxos** (and optional **APP_URL**) in `.env` (see below), and start the app.

### Option B: Manual setup

1. **Install PHP dependencies**

   ```bash
   composer install
   ```

2. **Environment file**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database**

   Default `.env` uses **SQLite**. Ensure the file exists:

   ```bash
   touch database/database.sqlite
   ```

   For MySQL/PostgreSQL, set `DB_*` in `.env` instead of SQLite.

4. **Migrate**

   ```bash
   php artisan migrate
   ```

5. **Frontend assets**

   ```bash
   npm install
   npm run build
   ```

   For active UI work, use `npm run dev` (often together with `php artisan serve`; see **Running** below).

6. **Paxos API** (required for identity/account flows against Paxos)

   In `.env`:

   ```env
   PAXOS_BASE_URL=https://api.sandbox.paxos.com
   PAXOS_API_TOKEN=your_api_token
   PAXOS_CLIENT_ID=your_client_id
   PAXOS_CLIENT_SECRET=your_client_secret
   PAXOS_SCOPE=identity:write_identity identity:read_identity identity:write_account identity:read_account
   ```

   Set `APP_URL` to match how you access the app (e.g. `http://127.0.0.1:8000`).

7. **Seed demo users (optional)**

   ```bash
   php artisan db:seed
   ```

   See `database/seeders/UserSeeder.php` for emails/passwords (default password is `password`).

### Running the application

**All-in-one dev stack** (Laravel server, queue worker, logs, Vite):

```bash
composer run dev
```

**Minimal** (two terminals):

```bash
php artisan serve
```

```bash
npm run dev
```

Then open the URL shown by `serve` (typically `http://127.0.0.1:8000`).

**Production-style assets** (no Vite dev server): after `npm run build`, only `php artisan serve` (or your web server) is needed.

### Tests

```bash
composer run test
```

## Usage

### Registration and Login

1. Visit the registration page and create an account with the "Lender" role
2. Login with your credentials
3. You'll be redirected to the dashboard

### Creating an Identity

1. Navigate to "Identities" in the navigation menu
2. Click "Create Identity"
3. Fill in all required fields:
   - Personal information (name, date of birth, nationality, email, phone)
   - Address information
   - Optional: CIP ID (SSN) information
4. Submit the form
5. The identity will be created both locally and in Paxos
6. In sandbox mode, you can approve the identity by clicking "Approve (Sandbox)"

### Creating an Account

1. Navigate to "Accounts" in the navigation menu
2. Click "Create Account"
3. Select an approved identity
4. Choose account type (Brokerage, Custody, or Other)
5. Optionally add a description
6. Check "Create profile automatically" if you want a profile created
7. Submit the form
8. The account will be created both locally and in Paxos

### Viewing Profiles

1. Navigate to "Profiles" in the navigation menu
2. View all profiles associated with your accounts
3. Click on a profile to see detailed information

## API Endpoints

The application implements the following Paxos API endpoints based on the Postman collection:

- `POST /v2/identity/identities` - Create identity
- `GET /v2/identity/identities` - List identities
- `PUT /v2/identity/identities/{id}/sandbox-status` - Approve identity (sandbox)
- `POST /v2/identity/accounts` - Create account (with optional profile)
- `GET /v2/identity/accounts` - List accounts

## Project Structure

```
app/
├── Http/
│   └── Controllers/
│       ├── Auth/          # Authentication controllers
│       ├── Lender/        # Lender-specific controllers
│       └── DashboardController.php
├── Models/
│   ├── User.php
│   ├── Identity.php
│   ├── Account.php
│   └── Profile.php
└── Services/
    └── PaxosService.php   # Paxos API integration

database/
└── migrations/            # Database migrations

resources/
└── views/
    ├── layouts/          # Layout templates
    ├── auth/             # Authentication views
    ├── lender/           # Lender views
    └── dashboard.blade.php
```

## Roles

- **Admin**: Full access to all features
- **Lender**: Can create and manage identities, accounts, and profiles
- **Borrower**: (Future implementation)

## Notes

- The application uses the Paxos sandbox environment by default
- Identity approval is available in sandbox mode for testing
- All API calls are logged for debugging purposes
- The application stores both local data and Paxos IDs for reference

## Troubleshooting

- **API Token Issues**: Make sure your Paxos credentials are correctly set in `.env`
- **Database Issues**: Ensure migrations have been run and the database file exists (for SQLite)
- **Vite / manifest errors**: Run `npm run dev` while developing, or `npm run build` for a production-style asset build
- **Authentication Issues**: Clear cache with `php artisan cache:clear` and `php artisan config:clear`
