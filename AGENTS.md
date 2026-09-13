# AGENTS.md

## Project Overview

This repository is a Laravel 10 application using PHP, Blade, Vite, and Laravel Boost.

## Setup

- Install PHP dependencies with `composer install`.
- Install frontend dependencies with `npm install`.
- Configure the local environment in `.env` and generate an application key with `php artisan key:generate` when needed.
- Run database migrations with `php artisan migrate`.

## Development

- Start the Laravel application with `php artisan serve`.
- Start the Vite development server with `npm run dev`.
- Build frontend assets with `npm run build`.
- Inspect available Laravel commands with `php artisan list`.

## Testing and Quality

- Run the test suite with `php artisan test` or `vendor/bin/phpunit`.
- Format PHP code with `vendor/bin/pint`.
- Run a focused test file when possible, for example `php artisan test tests/Feature/ExampleTest.php`.
- Keep tests close to the behavior they cover and update them when behavior changes.

## Laravel Conventions

- Put HTTP controllers and middleware in `app/Http`.
- Put Eloquent models in `app/Models`.
- Put web routes in `routes/web.php` and API routes in `routes/api.php`.
- Put schema changes in timestamped files under `database/migrations`.
- Prefer Laravel and existing project abstractions over introducing custom infrastructure.
- Use configuration and environment variables through Laravel's config system; do not hardcode credentials or environment-specific values.

## Editing Guidelines

- Keep changes focused and preserve existing style.
- Do not edit files under `vendor/` or generated files under `storage/`.
- Do not commit secrets, local `.env` files, or generated build artifacts.
- Run the narrowest relevant test or quality check after making changes, then run the broader suite when the change affects shared behavior.
- Laravel Boost is installed as a development dependency and can be updated with `php artisan boost:update` when explicitly needed.
