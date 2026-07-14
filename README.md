# Budgeting and Expense Management System (PHP + MySQL)

A complete, working web application implementing the full spec: role-based
access, budget allocation settings, denomination tracking, income & expense
entry with a 4-level expense approval workflow, asset register, and full
reporting suite (Budget Allocation, Expense, Income Statement, Balance Sheet,
Cash Flow) plus a KPI/chart dashboard.

## Requirements
- PHP 8.0+ (uses PDO, password_hash)
- MySQL 5.7+ / MariaDB 10.3+ (uses generated columns)
- A web server (Apache/Nginx) or PHP's built-in server for quick testing

No external PHP packages are required — everything is plain PHP with
Bootstrap 5 / Bootstrap Icons / Chart.js loaded from CDN in the browser.

## 1. Import the database

```bash
mysql -u root -p < sql/schema.sql
```

This creates the `budget_system` database, all tables, and seed data
(default budget allocation percentages, 4 approver placeholders, treasurer
profile, bill denominations, roles, and two default user accounts).

## 2. Configure the database connection

Edit `config/config.php` and set:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'budget_system');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

If you're hosting the app in a sub-folder (e.g. `http://localhost/budget-system/`),
update `BASE_URL` accordingly (e.g. `'/budget-system/'`).

## 3. Set default passwords (required — one-time step)

The schema ships with placeholder password hashes that will **not** work.
Run this once, either via CLI or by visiting it in your browser:

```bash
php tools/setup_passwords.php
```

This sets:
- `admin` / `admin123` → Super Admin
- `treasurer` / `treasurer123` → Treasurer

**Delete `tools/setup_passwords.php` after running it once**, for security.

## 4. Run

Quick local test with PHP's built-in server (from the project root):

```bash
php -S localhost:8000
```

Then open `http://localhost:8000/login.php`.

For production, point your Apache/Nginx document root at this folder and
make sure `config/`, `includes/`, `sql/`, and `tools/` are not web-accessible
if your host doesn't already restrict dotfiles/PHP includes (optional
hardening — the app works fine as-is on a single-vhost setup).

## Roles & Permissions

| Role | Access |
|---|---|
| Super Admin | Everything: Settings, Users, Income, Expenses, Assets, Reports |
| Treasurer | Income, Expenses, Denomination, Assets, Reports |
| Approver 1–3 | Approve Expenses queue (their level only) |
| Approver 4 | Approve Expenses queue — final approval |

Approvers and Treasurer contact-info records (under Settings) are separate
from login accounts — assign a login account with the matching role
(`approver_1`...`approver_4`, `treasurer`) under **Settings → Users** so
the right person can act in the approval queue.

## Expense Approval Workflow

1. Treasurer records an expense → status `Pending`.
2. Approver 1 approves → `Approved by L1`. Approver 2 approves → `Approved by L2`.
3. Approver 3 approves → `Approved by L3`. Approver 4 approves → `Approved` (final).
4. Any approver can reject at their stage → status `Rejected` (end of workflow).
5. Only `Approved` expenses count toward reports, dashboard KPIs, and budget
   utilization.

Super Admin can act at any approval level from the same **Approve Expenses**
screen (useful for catching up on backlog or covering for an absent approver).

## Module Map

```
index.php                     Dashboard (KPIs + Chart.js charts)
login.php / logout.php         Authentication
settings/budget_allocation.php Budget % settings (must total ≤100%)
settings/approvers.php         4 approver contact profiles
settings/treasurer.php         Treasurer contact profile
settings/users.php              User accounts & roles
denomination/index.php          Bill denomination entry + cash summary + allocation report
income/index.php                Income entry + weekly/monthly/quarterly/yearly report
expenses/index.php               Expense entry + weekly/monthly/quarterly/yearly report
expenses/approve.php             4-level approval queue
assets_module/index.php          Asset register (CRUD)
reports/budget_allocation.php    Allocation vs spend vs remaining
reports/expense_report.php       Filterable expense report (category, date range)
reports/income_statement.php     Income - Expenses = Net Income
reports/balance_sheet.php         Assets / Liabilities / Equity (+manage bank/loan/payable)
reports/cash_flow.php             Operating/Investing/Financing + beginning/ending cash
```

## Notes on data model choices

- **Fund Source** on an expense links loosely (by name) to a Budget
  Allocation item, so the Budget Allocation Report can show spend vs.
  remaining per category.
- **Cash on hand** and the Cash Flow Statement's cash figures are derived
  from recorded **Denomination** entries (the physical bill count), matching
  the spec's cash-management workflow.
- **Bank balance, loans, and payables** are manually recorded ledger lines
  (Settings-like mini-CRUD embedded directly in the Balance Sheet report)
  since the spec doesn't define a dedicated bank/loan module.
- Passwords are stored with PHP's `password_hash()` (bcrypt) — never in
  plain text.
- All forms are CSRF-protected and all SQL uses PDO prepared statements.
