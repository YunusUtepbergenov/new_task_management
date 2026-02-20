# CERR Task Management System

Enterprise task management platform, built with Laravel 12. Manages task assignment, employee KPI scoring, document workflows, and Telegram-based notifications.

## Tech Stack

- **Backend:** PHP 8.2, Laravel 12, Laravel Jetstream, Sanctum
- **Frontend:** Livewire 4, Alpine.js 3, Tailwind CSS 3
- **Build Tool:** Vite 6
- **Database:** MySQL
- **Exports:** Maatwebsite Excel

## Requirements

- PHP >= 8.2
- Composer
- Node.js & npm
- MySQL 8.0+

## Installation

1. **Clone the repository**

```bash
git clone <repository-url>
cd new_task_management
```

2. **Install PHP dependencies**

```bash
composer install
```

3. **Install frontend dependencies**

```bash
npm install
```

4. **Environment setup**

```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure `.env`**

Set the following variables:

```dotenv
# Application
APP_NAME="Name of Application"
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database
DB_USERNAME=username
DB_PASSWORD=password

# Mail (for password reset, notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com

# Telegram Bot (optional)
TELEGRAM_BOT_TOKEN=your-bot-token
TELEGRAM_WEBHOOK_SECRET=your-webhook-secret
```

6. **Run migrations and seed the database**

```bash
php artisan migrate
```

7. **Build frontend assets**

```bash
npm run build
```

8. **Start the application**

```bash
php artisan serve
```

For development with hot-reload:

```bash
npm run dev        # in one terminal
php artisan serve  # in another terminal
```

## Artisan Commands

### Custom Commands

| Command | Description |
|---------|-------------|
| `php artisan tasks:generate-repeats` | Generate recurring tasks (weekly, monthly, quarterly) |
| `php artisan telegram:set-webhook {url?}` | Register Telegram bot webhook URL |
| `php artisan telegram:deadline-reminders` | Send Telegram reminders for tasks due today |

### Scheduled Tasks

These commands run automatically via Laravel's scheduler (`php artisan schedule:work`):

| Schedule | Command | Description |
|----------|---------|-------------|
| Daily 00:01 | Overdue check | Marks overdue tasks |
| Daily 01:00 | `tasks:generate-repeats` | Generates recurring tasks |
| Daily 08:00 | `telegram:deadline-reminders` | Sends deadline reminders via Telegram |

To run the scheduler locally:

```bash
php artisan schedule:work
```

### Common Laravel Commands

```bash
php artisan migrate              # Run database migrations
php artisan migrate:fresh --seed # Reset database and seed
php artisan db:seed              # Seed the database
php artisan test --compact       # Run tests
php artisan config:clear         # Clear config cache
php artisan cache:clear          # Clear application cache
php artisan route:list           # List all routes
```

## NPM Scripts

| Command | Description |
|---------|-------------|
| `npm run dev` | Start Vite dev server with hot-reload |
| `npm run build` | Build production assets |
| `npm run preview` | Preview production build |

## Features

### Task Management
- Create, edit, and track tasks with deadlines
- Assign tasks to multiple users (group tasks)
- Recurring tasks (weekly, monthly, quarterly)
- File attachments and response submissions
- Task scoring and KPI evaluation

### Role-Based Access
- **Director** -- Full access, task evaluation
- **Deputy Director** -- Task creation and evaluation
- **Sector Head** -- Manages sector tasks and employees
- **Researcher** -- Views and completes assigned tasks

### Reports & Analytics
- Weekly task reports with Excel export
- KPI scoring and performance reports
- Employee workload tracking

### Document Management
- Articles, digests, notes, and research journals
- File upload/download support
- Multilingual journals (Russian/Uzbek)

### Telegram Integration
- Bot notifications for new tasks
- Daily deadline reminders
- Task status updates

### Employee Management
- Attendance tracking
- Vacation management
- Profile settings

## API Endpoints

Protected by Sanctum authentication:

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/user` | Get authenticated user |
| `POST` | `/api/telegram/verify-token` | Verify Telegram auth token |
| `POST` | `/api/telegram/get-user-tasks` | Get user tasks via Telegram |
| `POST` | `/api/telegram/webhook` | Telegram webhook handler |

## Testing

```bash
# Run all tests
php artisan test --compact

# Run specific test file
php artisan test --compact tests/Feature/PasswordResetTest.php

# Run specific test by name
php artisan test --compact --filter=testName
```

## Timezone

The application is configured for `Asia/Tashkent` (UTC+5).