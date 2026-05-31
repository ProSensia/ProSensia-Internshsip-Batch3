# ProSensia Internship Portal — PHP / MySQL / Bootstrap

A premium, production-ready PHP port of the ProSensia portal. Drop into XAMPP / WAMP / cPanel and run.

## Tech stack
- **PHP 8+** (PDO MySQL)
- **MySQL 5.7+ / MariaDB 10+**
- **Bootstrap 5.3** + Bootstrap Icons (via CDN)
- **Custom dark "glass" theme** in `assets/css/style.css`

## 1. Install

### Option A — XAMPP (Windows / Mac / Linux)
1. Copy the entire `prosensia-portal-php/` folder to `htdocs/prosensia/`.
2. Start **Apache + MySQL** from the XAMPP control panel.
3. Open **phpMyAdmin** → click **Import** → choose `sql/schema.sql` → **Go**.
4. Open `http://localhost/prosensia/` in your browser.

### Option B — cPanel / Namecheap shared hosting
1. Upload the contents of `prosensia-portal-php/` to `public_html/` (or a subfolder).
2. In cPanel → **MySQL Databases**, create a database (e.g. `cpuser_prosensia`) and a user with all privileges.
3. Open **phpMyAdmin**, select that database, click **Import**, upload `sql/schema.sql`.
4. Edit `includes/connection.php` and set:
   ```php
   $DB_HOST = 'localhost';
   $DB_NAME = 'cpuser_prosensia';
   $DB_USER = 'cpuser_prosensia';
   $DB_PASS = 'your-strong-password';
   ```
5. Visit `https://yourdomain.com/`.

## 2. Demo accounts (all password: **`password123`**)

| Role          | Email                       |
|---------------|-----------------------------|
| Super Admin   | `admin@prosensia.com`       |
| Management    | `manager@prosensia.com`     |
| Mentor        | `mentor@prosensia.com`      |
| Intern        | `intern@prosensia.com`      |
| Intern (alt)  | `ali@prosensia.com`         |

## 3. Folder layout

```
prosensia-portal-php/
├── index.php              ← redirects to role home
├── login.php / logout.php
├── assets/css/style.css   ← premium dark "glass" theme
├── includes/
│   ├── connection.php     ← EDIT DB CREDENTIALS HERE
│   ├── auth.php           ← session + RBAC helpers
│   ├── header.php / footer.php / sidebar.php
├── admin/                 ← Super Admin only
│   ├── index.php · users.php · security.php
├── management/            ← Management dashboard
├── mentor/                ← Mentor hub + task assignment
├── intern/                ← Intern workspace
│   ├── index.php · profile.php · enrollment.php
│   ├── tasks.php · assignments.php · formc.php
├── shared/                ← Cross-role modules
│   ├── certificates.php · materials.php
│   ├── teams.php · messages.php · subscriptions.php
└── sql/schema.sql         ← Full schema + seed data
```

## 4. Modules included

- **Role-based dashboards** — Super Admin · Management · Mentor · Intern
- **Daily Tasks** — single-day OR multi-day sprints with checkpoints
- **Mentor task assignment** — assign to one intern or everyone, with cadence selector
- **Assignments** — intern submits GitHub URL, mentor grades + feedback
- **Form C** — full reflection form, admin/management review inbox
- **Certificates** — workflow gated by approved Enrollment + Form C, Super Admin issues with serial, grade, mentor rating, ProSensia logo, batch info, and QR code
- **Teams** — create groups with members, each gets a chat channel
- **Messages** — `#announcements` (Super Admin only), `#general`, team channels, direct messages
- **Materials** — PDFs, videos, links, organized by module
- **Subscriptions** — Starter / Pro / Full pricing + payment proof flow
- **Security** — posture dashboard + control checklist
- **User management** — invite, role assignment, deletion

## 5. Security notes

- Passwords are **bcrypt hashed** (`password_hash`).
- All queries use **PDO prepared statements**.
- All HTML output is escaped via `htmlspecialchars` (`e()` helper).
- Sessions enforce **role guards** via `require_role([...])`.
- For production: enable HTTPS, add rate limiting (e.g. `fail2ban` or in-app), set strong DB user passwords, and rotate the bcrypt cost factor if needed.

## 6. Customization

- **Branding** — Edit the `.logo-mark` and `.brand-name` in `includes/sidebar.php` and `login.php`. To use an image logo, drop `logo.png` into `assets/` and swap the `<div class="logo-mark">` blocks for `<img src="...">`.
- **Theme colors** — Adjust the CSS variables at the top of `assets/css/style.css` (`--primary`, `--bg`, etc.).
- **Tracks / batches** — Edit the dropdowns in `intern/enrollment.php`.

## 7. Troubleshooting

- **"Database connection failed"** — Check `includes/connection.php` credentials; confirm `sql/schema.sql` was imported.
- **Blank page** — Enable PHP errors temporarily: add `ini_set('display_errors',1); error_reporting(E_ALL);` at the top of `includes/connection.php`.
- **"Headers already sent"** — Make sure there's no whitespace before the opening `<?php` tag in any include file.

---

Built with ❤️ for ProSensia — Batch 3.
