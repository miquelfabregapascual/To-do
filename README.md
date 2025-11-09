# To-do

A Laravel 12 + Jetstream (Livewire) application for managing personal tasks. Authenticated users can capture tasks with due dates and optional descriptions, review them on a weekly calendar, and jump to focused lists such as Inbox, Today, Completed, or All tasks.

## Features

- **Weekly dashboard** with previous/next navigation and a quick-add form for new tasks.
- **Simple list views** for Inbox, Today, Completed, and All tasks using the same reusable Blade template.
- **Task lifecycle** support for creating, toggling completion, and deleting your own tasks.
- **Authentication and profiles** provided by Jetstream/Fortify, including email verification and optional two-factor setup.

## Prerequisites

Make sure the following tools are available on your machine:

- PHP 8.2+
- Composer 2
- Node.js 18+ and npm
- A database supported by Laravel (MySQL is the default in `.env.example`; SQLite also works for local development)

## Local Setup

1. **Clone the repository** and install PHP dependencies:
   ```bash
   git clone <repo-url>
   cd To-do
   composer install
   ```

2. **Install frontend dependencies**:
   ```bash
   npm install
   ```

3. **Configure environment variables**:
   ```bash
   cp .env.example .env
   ```
   Update database credentials in `.env` to match your local setup (for SQLite, set `DB_CONNECTION=sqlite` and point `DB_DATABASE` to a writable path).

4. **Generate the application key and run migrations**:
   ```bash
   php artisan key:generate
   php artisan migrate
   ```

5. **Run the development servers** (in separate terminals):
   ```bash
   php artisan serve
   npm run dev
   ```
   Visit `http://localhost:8000`, register a new account, and start adding tasks.

Jetstream ships with email verification. If you do not have outbound mail configured locally, you can disable the `verified` middleware in `routes/web.php` while testing.

## Testing

Run the automated test suite with:
```bash
php artisan test
```

## Additional Scripts

For a combined development workflow with Laravel's queue listener and Vite, you can also use the Composer script:
```bash
composer dev
```
This runs `php artisan serve`, `php artisan queue:listen`, and `npm run dev` concurrently.

## Useful Artifacts

- Primary task logic: `app/Http/Controllers/TaskController.php`
- Blade layout for the calendar dashboard: `resources/views/dashboard.blade.php`
- Simplified list view shared by Inbox/Today/Completed/All: `resources/views/tasks/simple-list.blade.php`

