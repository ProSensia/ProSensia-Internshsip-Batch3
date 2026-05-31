# ProSensia Internship Portal — PHP / MySQL / Bootstrap

A full-stack PHP + MySQL portal for managing a coding internship cohort
(Super Admin, Management, Mentor, Intern roles).

## What's new in this build

- **ProSensia logo** wired into sidebar, topbar, login and signup (upload your own from Admin → Settings).
- **Fully responsive** — sidebar collapses to a slide-in drawer on mobile/tablet, tables scroll, KPIs reflow.
- **Form C → PDF download** (`intern/formc_pdf.php`) — generated with **FPDF** in the *Internship Placement Proforma* layout: student info, employer block, signature & stamp lines for Employer and HoD/Chairman, plus optional reflection page. Logo + partner logo (Pak-Austria) appear in the header automatically.
- **Multi-step animated signup** (`signup.php`) — 4 steps (account → personal → academic → skills). New accounts land as **pending**; only the **super admin** can see full profiles and approve/reject from *Users & Approvals*.
- **Attendance** (`shared/attendance.php`) — daily check-in / check-out / leave, 30-day history, mentor & management see today's roll.
- **Materials** — mentors, management and super admin can publish lectures, PDFs and links, scoped to a team or to everyone.
- **Role management** — super admin can change any user's role inline from the Users page.
- **Subscriptions** — super admin can create / update / cancel plans per user; users see their own.
- **Admin Settings** — upload ProSensia logo, upload partner logo (e.g. Pak-Austria), edit certificate batch & signatory.
- **Schema upgrades** — `attendance`, `subscriptions`, `settings` tables + extended `profiles` (CNIC, reg #, father name, semester, address) + `materials.team_id` + `team_members.role_in_team` + `users.status` now includes `pending`/`rejected`.

## Run it

1. Copy the `webfomat/` folder into your web root (XAMPP `htdocs/`, MAMP, or cPanel `public_html/`).
2. In phpMyAdmin, import `sql/schema.sql`.
3. Edit `includes/connection.php` if your MySQL host/user/pass differ from `root` / no password.
4. Browse to `http://localhost/webfomat/login.php`.

### Demo accounts (password: `password123`)

| Role          | Email                       |
| ------------- | --------------------------- |
| Super Admin   | admin@prosensia.com         |
| Management    | manager@prosensia.com       |
| Mentor        | mentor@prosensia.com        |
| Intern        | intern@prosensia.com        |
| Intern (2nd)  | ali@prosensia.com           |
| **Pending**   | zara@prosensia.com (test approval flow) |

## Folder layout

```
webfomat/
├── admin/         Super-admin only (index, users, settings, security)
├── management/    Management dashboard
├── mentor/        Mentor hub + assign-task
├── intern/        Intern workspace (enrollment, profile, tasks, assignments, formc, formc_pdf)
├── shared/        Materials, Messages, Teams, Certificates, Attendance, Subscriptions
├── includes/      auth, connection, header, footer, sidebar
├── lib/           FPDF (PDF generation, no Composer required)
├── assets/        css, img/prosensia-logo.png
├── uploads/       admin-uploaded logos / partner brand
├── sql/schema.sql Full schema + seed data
├── login.php  signup.php  logout.php  index.php
└── README.md
```

## Notes

- The bundled ProSensia logo is a placeholder generated for this build — replace it any time from **Admin → Settings**.
- The Pak-Austria (partner) logo slot is empty by default; upload from **Admin → Settings** and it appears in the Form C PDF header next to the ProSensia logo.
- PDF generation uses **FPDF 1.8.6** (single-file, pure PHP) — no Composer, no `pdflib`, no system binaries required. Works on any shared host with PHP 7.4+.
- All passwords are bcrypt-hashed (`password_hash`). Demo seed accounts also accept the literal `password123` for first-run convenience.
