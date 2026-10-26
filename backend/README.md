# Document Management System — backend

Laravel 12 API for document and folder management, permissions, audit history, public share links, image albums, and user authentication.

## Requirements

- PHP 8.2 or newer, with the extensions required by Laravel plus GD and ZIP.
- Composer 2.
- PostgreSQL for a normal local installation (SQLite is configured for automated tests).
- Node.js 20 or newer for the backend's optional Vite assets.

## Local setup

From this directory:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Set the database connection and credentials in `.env`, then configure the exact browser origins that may call the API:

```dotenv
FRONTEND_ALLOWED_ORIGINS=http://localhost:5173
```

Use a comma-separated list for multiple origins. Do not use `*` with credentialed requests. Configure production origins in the deployment environment; the example value is for local development only.

Run migrations and start the API and queue worker in separate terminals:

```powershell
php artisan migrate
php artisan serve
php artisan queue:work
```

Start the React application from `..\frontend` in another terminal. The frontend uses `VITE_API_BASE_URL`; its default is `http://localhost:8000/api`.

## Important operational notes

- Document and image originals are stored on the private filesystem disk. Do not expose that disk through a public web server or `storage:link`.
- Queued image processing and document text extraction require a running worker. Text extraction currently supports plain-text files; unsupported document formats are marked failed rather than receiving placeholder text.
- The recovery-code encryption migration uses the configured Laravel `APP_KEY`. Keep the same key when running migrations and do not rotate it without a data re-encryption plan.
- Authentication uses an HttpOnly `api_token` cookie. Use HTTPS in production and configure only trusted frontend origins.

## Quality checks

```powershell
php artisan test
php vendor\bin\pint --test
```

The test suite uses SQLite in memory as configured in `phpunit.xml`. Frontend checks and build instructions are in `..\frontend\README.md`.

Pint currently reports formatting issues across the existing backend. CI gates the test suite while that baseline is cleaned up; keep running Pint locally before submitting PHP changes.
