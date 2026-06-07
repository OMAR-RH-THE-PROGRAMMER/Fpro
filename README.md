# ClinicDesk — Clinic Management Dashboard

PHP Final Project | SDEV 2106 / WDMM 2010 / MOBC 2102 | Semester 2, 2025–2026

---

## 🚀 Quick Setup

### 1. Place the project in your web root

```
C:/xampp/htdocs/clinicdesk/    (Windows XAMPP)
/var/www/html/clinicdesk/      (Linux Apache)
```

### 2. Import the database

In phpMyAdmin:
1. Create database `clinicdesk_db`
2. Import `clinicdesk_db.sql`

**Or** via terminal:
```bash
mysql -u root -p < clinicdesk_db.sql
```

### 3. Configure database credentials

Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'clinicdesk_db');
define('DB_USER', 'root');
define('DB_PASS', '');   // your MySQL password
```

### 4. Configure the base URL

Edit `config/config.php`:
```php
define('BASE_URL', 'http://localhost/clinicdesk');
```

### 5. Enable mod_rewrite (Apache)

Make sure `mod_rewrite` is enabled and `AllowOverride All` is set for your htdocs directory.

---

## 🔑 Default Login Credentials

| Role    | Email                  | Password   |
|---------|------------------------|------------|
| Admin   | admin@clinic.local     | password   |
| Doctor  | doctor@clinic.local    | password   |
| Doctor  | doctor2@clinic.local   | password   |
| Patient | patient@clinic.local   | password   |

> ⚠️ **Change all passwords immediately after first login!**

---

## 📁 Project Structure

```
clinicdesk/
├── index.php              ← Front controller (all routes go here)
├── .htaccess              ← Forces all requests through index.php
├── clinicdesk_db.sql      ← Database schema + seed data
├── config/
│   ├── config.php         ← App settings (BASE_URL, limits...)
│   └── database.php       ← DB credentials (keep out of git!)
├── core/
│   ├── Database.php       ← Singleton mysqli wrapper
│   ├── Auth.php           ← Session-based authentication
│   ├── CSRF.php           ← CSRF token generation & validation
│   ├── Paginator.php      ← Pagination helper
│   └── helpers.php        ← Utility functions (redirect, e(), url()...)
├── models/                ← One model per table, all use prepared statements
├── controllers/           ← Business logic, one per feature area
├── views/                 ← PHP HTML templates using AdminLTE 3
│   ├── partials/          ← header, navbar, sidebar, footer, alerts
│   ├── auth/              ← login page
│   ├── dashboard/         ← admin / doctor / patient dashboards
│   ├── users/             ← user CRUD
│   ├── doctors/           ← doctor management + specializations
│   ├── appointments/      ← book, list, view, status update
│   ├── prescriptions/     ← add (doctor), view/download (patient)
│   ├── reports/           ← admin reports + CSV export
│   └── errors/            ← 403, 404
└── public/
    ├── assets/adminlte/   ← AdminLTE CSS/JS (local, no CDN)
    └── uploads/
        ├── avatars/
        ├── doctor_photos/
        └── prescriptions/ ← BLOCKED from direct URL access
```

---

## ✅ Features Implemented

- **Role-based access**: Admin / Doctor / Patient — all through one login page
- **Singleton DB**: `Database::getInstance()` — one connection reused everywhere
- **OOP Models**: All 5 tables have dedicated Model classes extending `BaseModel`
- **Prepared Statements**: Zero raw SQL — every query uses `?` placeholders
- **CSRF Protection**: Every POST form carries a hidden token validated with `hash_equals()`
- **Session Auth**: `session_regenerate_id(true)` on login, role checked on every action
- **File Uploads**: `getimagesize()` for images, `finfo_file()` for PDFs
- **Secure File Serving**: Prescriptions blocked via `.htaccess`, served through PHP with ownership check
- **Pagination**: `Paginator` class + `LIMIT/OFFSET` in prepared statements
- **Search & Filter**: Dynamic `WHERE` clause built in PHP from active filters
- **Dashboard Stats**: Live counts via `COUNT()`, `GROUP BY`, `JOIN` queries
- **Reports + CSV Export**: `fputcsv()` to `php://output` with proper headers
- **AdminLTE 3**: All assets local, no CDN dependency
- **XSS Prevention**: All output uses `htmlspecialchars()` via `e()` helper
- **Double-booking prevention**: `UNIQUE KEY (doctor_id, appt_date, appt_time)` + PHP conflict check

---

## 🔒 Security Notes

- Passwords stored with `password_hash(PASSWORD_BCRYPT)` only
- CSRF token on every POST
- No string interpolation in SQL — ever
- `display_errors = Off` — errors are logged, not shown
- Prescriptions folder has `.htaccess` deny rule
- `config/database.php` should be in `.gitignore`

---

## 📝 .gitignore

```
config/database.php
public/uploads/avatars/*
public/uploads/doctor_photos/*
public/uploads/prescriptions/*
!public/uploads/*/index.php
!public/uploads/prescriptions/.htaccess
```
