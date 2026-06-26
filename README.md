# Cron Scheduler for Magento 2

Take control of Magento's cron jobs from the admin panel — view every scheduled
job, reschedule or disable any of them without touching code, monitor execution
history, get notified when jobs fail, and run or queue a job on demand.

`DotCommerce_CronScheduler` is a single, self‑contained module with no third‑party
dependencies. It is built on Magento's native cron tables and service contracts,
so it stays out of the way and upgrades cleanly.

---

## Requirements

| Requirement | Version |
| ----------- | ------- |
| Magento     | 2.4.4 or higher (forward‑compatible through 2.4.9) |
| PHP         | 8.1, 8.2, 8.3, 8.4, or 8.5 |

---

## Installation

### Via Composer (recommended)

```bash
composer require dot-commerce/magento2-cron-scheduler
bin/magento module:enable DotCommerce_CronScheduler
bin/magento setup:upgrade
bin/magento cache:flush
```

### Manual installation

1. Copy the module into your Magento installation:

   ```
   app/code/DotCommerce/CronScheduler
   ```

2. Enable it and run setup:

   ```bash
   bin/magento module:enable DotCommerce_CronScheduler
   bin/magento setup:upgrade
   bin/magento cache:flush
   ```

> **Note:** This extension manages cron jobs but does not replace the system
> cron that drives them. Make sure Magento cron is configured and running
> (`bin/magento cron:run`, or a system crontab entry / scheduler add‑on). The
> *Last Cron Activity* banner will tell you whether cron is alive.

---

## Features

### Cron Jobs List
A grid of every cron job declared across all installed modules (`crontab.xml`),
kept in sync automatically.

- **Columns:** ID, Job Code, Group, Instance (class), Method, Schedule (original
  cron expression), Modified Schedule, Status.
- **Reschedule any job** — inline‑edit the *Modified Schedule* to override a job's
  cron expression. The original schedule is preserved, so the override is fully
  reversible (clear the field to revert).
- **Enable / disable any job** — inline‑edit the *Status*. A disabled job is
  removed from Magento's live cron configuration entirely, so it never runs.
- **Cron expression validation** — modified schedules are validated on save, with
  real per‑field range checks (e.g. minute `0–59`, hour `0–23`) that reject
  expressions Magento would otherwise accept but silently never run.
- **Filters** on every column and **mass actions**: Enable, Disable, Delete.
- **Run Job** (row action) — force‑run a job immediately, in‑process, with
  execution time and memory profiling recorded to the run history. Runs
  regardless of the job's schedule or enabled state.
- **Schedule Now** (row action) — queue a job to run on the next cron tick
  (non‑blocking; safe for long‑running jobs).

### Cron Tasks List
A read‑only view of Magento's native `cron_schedule` execution history.

- **Columns:** ID, Job Code, Status, Messages, Created At, Scheduled At,
  Executed At, Finished At.
- **Color‑coded statuses** — Success (green), Pending / Running (grey),
  Error / Missed (red).
- **Date‑range filters** on all four datetime columns, plus status and job‑code
  filters.
- **Mass Delete** to prune history.

### Last Cron Activity monitor
An "is‑cron‑alive" banner shown above the grids. It reports how long ago cron
last ran successfully, so a stalled cron is immediately obvious.

### Email failure notifications
Get notified by email the moment a cron job fails.

- **Per‑job alerts** — when a managed job errors, an email is sent to the
  configured recipients.
- **Templated subject and body** — compose your own message using the
  `{{job_code}}`, `{{executed_at}}`, and `{{message}}` variables.
- **Anti‑spam throttle** — a configurable *Notifications Time Filter* sends at
  most one alert per job within the chosen interval (set it to `0` to be notified
  of every failure).
- Configured under **Stores → Configuration → Dot Commerce → Cron Scheduler →
  Email Notification** (off by default).

### Automatic synchronization
A lightweight heartbeat cron (every minute) reconciles the job registry with
Magento's configuration: new jobs are added, changed schedules are refreshed,
and removed jobs are cleaned up (along with their orphaned schedule rows).

---

## Usage

After installation, the screens are available in the Magento admin under:

- **System → Cron Scheduler → Cron Jobs List**
- **System → Cron Scheduler → Cron Tasks List**

### Rescheduling a job
Open **Cron Jobs List**, find the job, and edit its **Modified Schedule** column
with a standard cron expression (e.g. `*/15 * * * *`). Save. The job now runs on
your expression instead of its default. Clear the field to restore the original.

### Disabling a job
Set a job's **Status** to *Disabled* (inline, or via the mass action). It is
removed from Magento's live cron configuration and will not be scheduled until
re‑enabled.

### Running a job on demand
Use the row actions on **Cron Jobs List**:

- **Run Job** — executes the job right now and reports how long it took.
- **Schedule Now** — queues the job to run on the next cron tick.

---

## Uninstall

```bash
bin/magento module:disable DotCommerce_CronScheduler
composer remove dot-commerce/magento2-cron-scheduler   # if installed via Composer
bin/magento setup:upgrade
bin/magento cache:flush
```

---

## License

This module is licensed under the [MIT License](LICENSE).

## Author

Mudassar Iqbal — <miqbal@dotcommerce.co>
© Dot Commerce
