# Web-Based Scheduling System with Constraint Algorithm

An HND Computer Science project: a scheduling system that lets administrators manage
users, resources (rooms/labs/halls/equipment), and time slots, and automatically
validates every schedule against hard constraints (room clash, lecturer clash, time
conflict, resource availability) before saving it.

**Stack:** HTML5, CSS3, JavaScript, Bootstrap 5, PHP 8+ (procedural, MySQLi), MySQL.
Runs on XAMPP, WAMP, or any standard PHP/MySQL localhost stack.

---

## 1. Project Structure

```
SchedulingSystem/
├── config/
│   └── db.php                 # DB connection + session bootstrap
├── database/
│   └── database.sql           # Full schema + sample data
├── includes/
│   ├── auth_check.php         # Login/role middleware, included on every protected page
│   ├── header.php             # <head>, navbar, opens layout wrapper
│   ├── sidebar.php             # Left navigation menu
│   └── footer.php             # Closes layout, loads Bootstrap/validation JS
├── users/                      # MODULE 6: User CRUD (admin only)
│   ├── manage_users.php
│   ├── add_user.php
│   ├── edit_user.php
│   └── delete_user.php
├── resources/                  # MODULE 7: Resource CRUD
│   ├── manage_resources.php
│   ├── add_resource.php
│   ├── edit_resource.php
│   └── delete_resource.php
├── timeslots/                  # MODULE 8: Time slot CRUD
│   ├── manage_timeslots.php
│   ├── add_timeslot.php
│   ├── edit_timeslot.php
│   └── delete_timeslot.php
├── constraints/                 # MODULE 9: Constraint catalogue CRUD
│   ├── manage_constraints.php
│   ├── add_constraint.php
│   ├── edit_constraint.php
│   └── delete_constraint.php
├── schedules/                   # MODULES 10-13: engine + schedule CRUD
│   ├── constraint_engine.php    # validate_schedule() — the core algorithm
│   ├── create_schedule.php
│   ├── view_schedules.php
│   ├── update_schedule.php
│   └── delete_schedule.php
├── css/style.css                # Global styles, color scheme, sidebar/cards
├── js/validation.js             # Client-side form validation helpers
├── images/                      # (empty — add a logo here if desired)
├── index.php                    # Entry point, redirects to login/dashboard
├── login.php / authenticate.php / logout.php   # MODULE 4
├── dashboard.php                # MODULE 5
├── reports.php                  # MODULE 14: reports, print, CSV export
├── reset_admin_password.php     # One-time setup helper — DELETE after use
├── test_cases.md                # MODULE 17: 32 test cases
└── README.md                    # This file
```

### Why this structure?
- **config/** isolates environment-specific settings (DB host/user/pass) in one file.
- **includes/** holds shared layout and the auth middleware so every page stays consistent
  and DRY (Don't Repeat Yourself).
- Each feature (**users, resources, timeslots, constraints, schedules**) gets its own
  folder with the same CRUD naming convention (`manage_`, `add_`, `edit_`, `delete_`),
  which makes the codebase predictable and easy to navigate/mark.
- **schedules/constraint_engine.php** is deliberately separated from the page logic —
  it is a pure function (`validate_schedule()`) reused by both create and update, so the
  constraint rules live in exactly one place.

---

## 2. Database Setup Guide

1. Start MySQL (via XAMPP/WAMP control panel).
2. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
3. Click **Import**, choose `database/database.sql`, and run it.
   - This creates the `scheduling_system` database with 5 tables
     (`users`, `resources`, `timeslots`, `constraints_tbl`, `schedules`),
     their primary/foreign keys, indexes, and sample rows.
4. **Important:** the sample `users` rows in the SQL file use placeholder password
   hashes that will not verify. After importing, visit
   `http://localhost/SchedulingSystem/reset_admin_password.php` **once** in your
   browser to generate real, working password hashes for the three sample accounts:
   - `admin` / `admin123` (role: admin)
   - `jsmith` / `lecturer123` (role: lecturer)
   - `mgarcia` / `lecturer123` (role: lecturer)
5. Delete `reset_admin_password.php` after running it (it's only needed once).

---

## 3. XAMPP Deployment Guide

1. Install [XAMPP](https://www.apachefriends.org/) (PHP 8+, MySQL/MariaDB, Apache).
2. Copy the entire `SchedulingSystem` folder into `C:\xampp\htdocs\` (Windows) or
   `/Applications/XAMPP/htdocs/` (Mac) / `/opt/lampp/htdocs/` (Linux).
3. Open the XAMPP Control Panel and **Start** the **Apache** and **MySQL** modules.
4. Follow the Database Setup Guide above (Section 2) to import the database.
5. Visit `http://localhost/SchedulingSystem/` in your browser.
6. You should be redirected to the login page. Log in with `admin` / `admin123`.

**WAMP users:** the process is identical — copy the folder into `www\`, start
Apache/MySQL from the WAMP tray icon, then browse to
`http://localhost/SchedulingSystem/`.

**Troubleshooting:**
- *"Database Connection Failed"* → confirm MySQL is running and that `config/db.php`'s
  `DB_USER`/`DB_PASS` match your MySQL setup (defaults: `root` / empty password, which
  is the XAMPP/WAMP default).
- *Blank white page* → enable PHP error display temporarily by adding
  `ini_set('display_errors', 1); error_reporting(E_ALL);` near the top of `config/db.php`,
  or check `xampp/php/logs/php_error_log`.
- *Login always says "Invalid username or password"* → you likely skipped running
  `reset_admin_password.php` after importing the database.

---

## 4. Administrator Manual

Log in as a user with the `admin` role to access every module.

| Task | Where |
|---|---|
| Create/edit/deactivate staff & lecturer accounts | Sidebar → **Users** (admin only) |
| Add rooms, labs, lecture halls, equipment | Sidebar → **Resources** |
| Define the days/times schedules can be booked into | Sidebar → **Time Slots** |
| View/edit the hard & soft constraint definitions | Sidebar → **Constraints** |
| Approve/reject is automatic — the algorithm decides | Sidebar → **New Schedule** |
| Review, search, filter, edit, or delete all bookings | Sidebar → **Schedules** |
| Generate/print/export reports | Sidebar → **Reports** |

**Managing conflicts:** you do not need to manually check for clashes — every time a
schedule is created or updated, `constraint_engine.php` automatically checks room
availability, room clashes, lecturer clashes, and time conflicts, and will refuse to
save (with a clear explanation) if any hard constraint is violated.

**Deactivating instead of deleting:** if a lecturer leaves temporarily, prefer setting
their account **Status = Inactive** (Edit User) over deleting them, so their historical
schedule records are preserved.

---

## 5. User Manual (Lecturer / Staff)

1. **Log in** with the username and password given to you by the administrator.
2. From the **Dashboard**, you can see a summary of the system and recent schedules.
3. To **book a resource**, go to **New Schedule**:
   - Enter a course/event title.
   - Choose an available resource, a time slot, and yourself (or the relevant lecturer)
     from the dropdowns.
   - Click **Run Constraint Validation & Save**. If your chosen combination clashes
     with an existing booking, you'll see exactly why (e.g. "Room clash" or "Lecturer
     clash") and can pick a different slot/resource.
4. To **review bookings**, go to **Schedules** — you can search, filter by status, and
   sort by day/time.
5. To **change a booking**, click the pencil icon next to it; the system re-runs the
   same conflict checks before saving your changes.
6. Click **Logout** (top-right) when you're done.

---

## 6. Security Notes

- Passwords are hashed with PHP's `password_hash()` (bcrypt) and verified with
  `password_verify()` — plaintext passwords are never stored.
- All database queries use **MySQLi prepared statements** with bound parameters,
  preventing SQL injection.
- All user-supplied output is passed through `htmlspecialchars()` before being echoed,
  preventing stored/reflected XSS.
- `includes/auth_check.php` blocks access to any protected page for logged-out users
  and enforces admin-only pages via `$require_admin = true`.
- Sessions auto-expire after 30 minutes of inactivity and are regenerated on login to
  prevent session fixation.
- Remember to delete `reset_admin_password.php` after first use in any real deployment.
