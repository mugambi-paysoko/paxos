# Paxos Laravel Application

A Laravel application for managing lender identities, accounts, and profiles with integration to the Paxos API.

## Features

- **User Authentication** with role-based access control (Admin, Lender, Borrower)
- **Lender Dashboard** with statistics and quick actions
- **Identity Management** - Create and manage lender identities
- **Account Management** - Create accounts linked to identities
- **Profile Management** - Automatically create profiles when creating accounts
- **Paxos API Integration** - Seamless integration with Paxos sandbox API

## Installation

1. **Clone/Navigate to the project directory:**
   ```bash
   cd /home/mugambi/paxos
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Set up environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database:**
   The project uses SQLite by default. Create the database file:
   ```bash
   touch database/database.sqlite
   ```
   
   Or configure MySQL/PostgreSQL in `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=paxos
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Configure Paxos API credentials in `.env`:**
   ```env
   PAXOS_BASE_URL=https://api.sandbox.paxos.com
   PAXOS_API_TOKEN=your_api_token
   PAXOS_CLIENT_ID=your_client_id
   PAXOS_CLIENT_SECRET=your_client_secret
   PAXOS_SCOPE=identity:write_identity identity:read_identity identity:write_account identity:read_account
   ```

6. **Run migrations:**
   ```bash
   php artisan migrate
   ```

7. **Start the development server:**
   ```bash
   php artisan serve
   ```

   The application will be available at `http://localhost:8000`

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
- **Authentication Issues**: Clear cache with `php artisan cache:clear` and `php artisan config:clear`
