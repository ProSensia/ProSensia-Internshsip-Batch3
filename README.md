# ProSensia Internship Portal (PHP / MySQL)

> Founder / Director / CEO: **Momin Khan** · EasyPaisa: **0310-7717890**

## Deploy in 5 minutes

1. Copy the `webfomat/` folder into your web root (`htdocs/`, `public_html/`, etc.).
2. Create a MySQL database and import `sql/schema.sql`.
3. Edit `includes/connection.php` with your DB credentials.
4. Make sure `assets/uploads/avatars/` is writable by PHP (chmod 775).
5. Open `http://localhost/webfomat/login.php`.

Demo accounts — all password `password123`:

| Role         | Email                       |
|--------------|-----------------------------|
| Super Admin  | momin@prosensia.com         |
| Management   | manager@prosensia.com       |
| Mentor       | mentor@prosensia.com        |
| Intern       | intern@prosensia.com        |

## What's new in this build (Phase 1)

- **Jira-style Kanban board** — personal board (`intern/board.php`) and per-team board (`shared/team_board.php`) with drag-and-drop between **To Do / In Progress / Done** (SortableJS).
- **Day-wise version control** — "Save daily report" snapshots each day into `kanban_snapshots` (one row per user per day).
- **Field-based teams** — schema seeded with Cyber Security, AI & ML, Full Stack, Python, QA, Graphic Designing, C++.
- **Profile images** — uploadable on `intern/profile.php`, stored under `assets/uploads/avatars/`, shown in topbar & user lists.
- **Animated signup wizard** — 4-step slide animation on `signup.php`.
- **Founder credit + EasyPaisa info** — footer on every page; super admin = Momin Khan.
- **Favicon** — falls back to the ProSensia logo automatically if `fav_path` setting is empty.
- **Form C PDF** — Pak-Austria proforma export via FPDF (`intern/formc_pdf.php`).

## Coming in Phase 2 (noted from your answers)

- Excel/CSV import of interns (columns: Name, Father's Name, University Registration #, Semester, Contact, Internship Field, Months Paid, Payment, CNIC) with BS Registration # as unique key.
- Subscription tiers: 1 mo = PKR 1,000, 2 mo = 1,800, 3 mo = 2,500 + scholarship flag + EasyPaisa screenshot upload + super-admin approval.
- Advanced chat with field-scoped channels.
- Super-admin edit / delete users + role re-assign + bulk approval.

## File map

```
webfomat/
├── admin/                 super-admin pages (users, settings, security)
├── api/                   AJAX endpoints (board_update.php)
├── assets/                CSS, images, uploads
│   └── uploads/avatars/   user profile images (writable)
├── includes/              auth, header, footer, sidebar, DB connection
├── intern/                intern workspace (board, tasks, profile, formc…)
├── lib/                   FPDF for PDF generation
├── management/  mentor/   role landing pages
├── shared/                team_board, materials, messages, attendance…
├── sql/schema.sql         full DB schema + seed data
└── login.php · signup.php · logout.php · index.php
```
