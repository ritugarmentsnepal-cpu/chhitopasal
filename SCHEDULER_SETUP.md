# Scheduler Setup Guide

## Why This Matters

This application uses Laravel's task scheduler for:
- **Pathao auto-sync** (`pathao:sync`) — syncs shipped order statuses automatically
- **Log pruning** (`logs:prune`) — cleans old activity logs daily

Without the scheduler running, Pathao auto-sync will never fire even if "Enable Auto Sync" is turned on in Settings.

---

## Production (Hostinger VPS + CloudPanel)

Add a single cron job via SSH or CloudPanel's Cron Job manager.

### Via SSH

```bash
crontab -e
```

Add this line (adjust the path to match your deployment path):

```
* * * * * cd /home/<your-user>/htdocs/chhitopasal.com && php artisan schedule:run >> /dev/null 2>&1
```

### Via CloudPanel

1. Go to **CloudPanel → Sites → Your Site → Cron Jobs**
2. Click **Add Cron Job**
3. Set schedule to: `* * * * *` (every minute)
4. Command: `cd /home/<your-user>/htdocs/chhitopasal.com && php artisan schedule:run`

> **Note:** Replace `/home/<your-user>/htdocs/chhitopasal.com` with the actual deployment path shown in CloudPanel under your site's file manager.

### Verify It's Working

```bash
php artisan schedule:list
```

You should see `pathao:sync` listed with its next run time.

---

## Local Development (XAMPP / Windows)

The scheduler runs automatically when you use:

```bash
composer dev
```

This starts `php artisan schedule:work` as one of the background processes (alongside the server, queue, and Vite).

> You do **not** need to set up Windows Task Scheduler for local development.

---

## Testing the Scheduler

To manually fire the sync command:

```bash
php artisan pathao:sync
```

To check what the scheduler would run right now:

```bash
php artisan schedule:run --verbose
```
