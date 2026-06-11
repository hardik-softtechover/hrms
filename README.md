# HRMS — Human Resource Management System

Laravel 12 + Inertia + Vue 3 + Tailwind CSS, MySQL.

## Features

### Employee
- **Profile** — upload avatar, edit contact / job details, change password
- **Attendance** — Check-in / Break-in / Break-out / Check-out; daily target 8h 30m; date-wise history with green (goal met) / red (short) progress bar
- **Leaves** — apply leave with half-day toggle (counts 0.5 day); month-wise history; remaining / paid / unpaid / pending balance
- **Directory** — searchable employee card grid
- **Events** — upcoming events with the next one highlighted; past events archive

### Admin (HR)
- Dashboard with key stats
- Add new employee with username/password (share creds → employee logs in & fills profile)
- Approve / reject leaves with optional note. If employee has no paid balance left, the approval is automatically recorded as **unpaid**.
- Create & delete company-wide events
- Leave approval triggers a **mail notification** to the employee (logged to `storage/logs/laravel.log` by default — switch `MAIL_MAILER` to SMTP/Mailgun/etc. in `.env` to send real emails).

## Setup

1. **Database** — create a MySQL database called `hrms` and update `.env`:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hrms
   DB_USERNAME=root
   DB_PASSWORD=YOUR_PASSWORD
   ```

2. **Migrate & seed**

   ```bash
   php artisan migrate:fresh --seed
   ```

3. **Build assets**

   ```bash
   npm run build      # or: npm run dev  (HMR)
   ```

4. **Run**

   ```bash
   php artisan serve
   ```

   Open <http://127.0.0.1:8000>

## Default credentials (from seeder)

| Role     | Username   | Password      |
|----------|------------|---------------|
| Admin    | `admin`    | `admin123`    |
| Employee | `employee` | `employee123` |

> Change these in production. The admin can create more employees from the **Employees** page.

## Mail

`MAIL_MAILER=log` is the default — leave-approval emails are written to `storage/logs/laravel.log`. Configure SMTP credentials in `.env` to deliver real emails.
