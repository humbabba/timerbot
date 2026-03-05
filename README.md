# Timerbot

**A collaborative meeting timer that keeps everyone on track.**

Timerbot divides your remaining meeting time equally among participants and counts each one down in real time. An operator runs the timer while participants watch their countdown on any device — no app install, no login required for public timers.

## How It Works

1. **Create a timer** — set your meeting end time, number of participants, and optional audio warnings.
2. **Share the link** — participants open the timer URL on their phone, laptop, or a shared screen.
3. **Run the meeting** — the operator starts the timer, advances between participants, and the time automatically redistributes as people finish early or run over.

The participant view updates in real time, showing a large countdown clock, the current participant number, time remaining per person, and overall meeting time left.

## Features

- **Fair time distribution** — remaining time is recalculated and redistributed after each participant finishes.
- **Live operator controls** — start, pause, next/previous participant, undo, and stop. Adjust end time and participant count on the fly.
- **Audio warnings** — configurable sound alerts at custom countdown offsets (before or after time expires). Nine built-in sounds generated via the Web Audio API — no external files needed.
- **Public and private timers** — public timers are viewable by anyone with the link. Private timers are restricted to group members.
- **Group-based access control** — every timer belongs to a group. Group members can run timers; group admins can edit and manage them.
- **Role-based permissions** — granular permissions for timers, users, roles, trash, settings, and activity logs. Roles define which other roles they can assign, preventing privilege escalation.
- **Custom participant terms** — call them "speakers", "presenters", "updates", or anything else.
- **Operator locking** — only one person can operate a timer at a time, with automatic lock expiry if the operator disconnects.
- **State persistence** — timer state saves to the server every second. Refresh the page or lose your connection and pick up right where you left off.
- **Overtime auto-reset** — forgotten timers automatically reset after a configurable overtime limit.
- **Passwordless auth** — magic link login via email. No passwords to remember or manage.
- **Soft delete with trash** — deleted items go to trash and can be restored or permanently removed.
- **Activity logging** — full audit trail of every create, update, delete, and run action with before/after change details.
- **Light and dark mode** — toggle between themes with a single click.
- **Screen wake lock** — keep the display awake while the timer is running, useful for shared screens.
- **App-wide settings** — configurable news/announcements on the home page, with unread badges in the nav.

## Tech Stack

- **[Laravel 12](https://laravel.com)** (PHP 8.2+)
- **[Tailwind CSS 4](https://tailwindcss.com)** for styling
- **[Alpine.js](https://alpinejs.dev)** for reactive UI components
- **[Vite](https://vite.dev)** for asset bundling
- **[Trix](https://trix-editor.org)** for rich text editing
- SQLite or MySQL for data storage

## Getting Started

```bash
# Clone the repository
git clone https://github.com/your-username/cortex.git
cd cortex

# Install dependencies and build
composer setup
```

The `composer setup` script installs PHP and Node dependencies, generates an app key, runs migrations, and builds frontend assets.

To seed the database with default roles, permissions, and an admin user:

```bash
php artisan db:seed
```

This creates an admin account with the email `admin@example.com`. Since Timerbot uses passwordless magic link authentication, you'll need to configure a mail driver in your `.env` to receive the login link or update the admin info in the PermissionSeeder.php file. 

For local development:

```bash
composer dev
```

This starts the Laravel dev server, queue worker, log tail, and Vite HMR concurrently.

## License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
