# Student Attendance System (Pure OOP PHP + MySQL)

A complete, presentation-ready BIT2 academic project: a Student Attendance
System built with **pure Object-Oriented PHP** (no framework) and **MySQL**
via PDO.

---

## 1. Demo Login Credentials

| Role    | Email                     | Password      |
|---------|---------------------------|----------------|
| Admin   | admin@school.edu          | Password123    |
| Teacher | mary.teacher@school.edu   | Password123    |
| Teacher | john.teacher@school.edu   | Password123    |

> These are **not shown on the login page** on purpose (as requested) — keep
> this document for your own reference and for your viva/demo.

If login ever fails because of a hash mismatch on your MySQL version, open
`database/generate_hash.php` in the browser to generate a fresh bcrypt hash
and paste it into the `users.password_hash` column via phpMyAdmin, then
delete that file.

---

## 2. Folder Structure

```
attendance-system/
├── config/
│   └── config.php            # DB credentials, encryption key, session start
├── classes/                  # ALL OOP classes live here
│   ├── interfaces/
│   │   └── Crudable.php       # CRUD contract (Abstraction)
│   ├── Database.php           # Singleton PDO connection (Encapsulation)
│   ├── Security.php           # Encrypt/decrypt + CSRF (Abstraction)
│   ├── Validator.php           # Form validation
│   ├── User.php               # ABSTRACT base class (Abstraction/Inheritance)
│   ├── Admin.php              # extends User (Inheritance/Polymorphism)
│   ├── Teacher.php            # extends User (Inheritance/Polymorphism)
│   ├── Auth.php                # login/logout/session guard
│   ├── Student.php             # implements Crudable
│   ├── ClassRoom.php           # implements Crudable
│   └── AttendanceRecord.php    # implements Crudable
├── includes/
│   ├── bootstrap.php          # loads config + all classes (include this first)
│   ├── header.php / footer.php / navbar.php
│   └── functions.php          # redirect(), flash messages, e() escaper
├── auth/
│   ├── login.php
│   └── logout.php
├── assets/css/style.css, assets/js/script.js
├── database/
│   ├── schema.sql              # full schema + sample data (run this first)
│   └── generate_hash.php       # utility to regenerate a password hash
├── index.php                   # redirects to login or dashboard
├── dashboard.php                # role-aware statistics dashboard
├── students.php / student_form.php / student_delete.php
├── classes_page.php / class_form.php / class_delete.php  (class = reserved word, hence "classes_page")
├── teachers.php / teacher_form.php / teacher_delete.php   (admin only)
├── attendance.php               # mark attendance per class/date
└── reports.php                  # search/filter + printable report
```

---

## 3. Installation Guide (Localhost / XAMPP / WAMP)

1. **Copy the project folder** into your server's web root:
   - XAMPP: `C:\xampp\htdocs\attendance-system`
   - WAMP: `C:\wamp64\www\attendance-system`
   - Linux/MAMP: your `htdocs`/`www` equivalent
2. **Start Apache and MySQL** from your control panel.
3. **Create the database:**
   - Open `phpMyAdmin` → Import → choose `database/schema.sql` → Go.
   - This creates the `student_attendance_system` database, all 5 tables,
     and inserts sample data + 3 demo accounts.
4. **Set your DB credentials** in `config/config.php` if different from
   the defaults (`root` / no password):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'student_attendance_system');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
5. **Set BASE_URL** in the same file to match your folder name, e.g.:
   ```php
   define('BASE_URL', '/attendance-system');
   ```
6. Visit `http://localhost/attendance-system/` in your browser.
7. Log in with the demo credentials above.

That's it — no Composer, no npm, no build step. Bootstrap 5 and Bootstrap
Icons are loaded from a CDN, so an internet connection is needed only for
the styling; all PHP/MySQL logic works fully offline.

---

## 4. Optional AWS Deployment (EC2 + RDS)

1. Launch an **EC2** instance (Ubuntu 22.04), open ports 22, 80, 443.
2. Install the stack:
   ```bash
   sudo apt update
   sudo apt install apache2 php php-mysql php-mbstring libapache2-mod-php mysql-server -y
   ```
3. Upload the project via `scp` or `git clone` into `/var/www/html/attendance-system`.
4. Either use the local MySQL server or create an **Amazon RDS (MySQL)**
   instance, then import `schema.sql` using:
   ```bash
   mysql -h <rds-endpoint> -u <user> -p < database/schema.sql
   ```
5. Update `config/config.php` with the RDS endpoint/credentials.
6. Set folder permissions: `sudo chown -R www-data:www-data /var/www/html/attendance-system`
7. Restart Apache: `sudo systemctl restart apache2`
8. (Optional) Attach an Elastic IP and point a domain at it; add HTTPS
   with Certbot/Let's Encrypt.

---

## 5. Where Each OOP Concept Is Demonstrated

| Concept            | Where |
|---------------------|-------|
| **Class & Object**  | Every file in `classes/` — e.g. `new Student(...)`, `new ClassRoom(...)` |
| **Constructor**     | Every class has `__construct()`, e.g. `Student::__construct()` sets all initial properties |
| **Encapsulation**   | Properties are `private`/`protected` everywhere (e.g. `Database::$connection` is private; `Student::$phone` only reachable via getters); `Validator::$errors` is private |
| **Inheritance**     | `Admin extends User`, `Teacher extends User` — both inherit `getId()`, `getFullName()`, etc. from the abstract `User` class |
| **Polymorphism**    | `$user->getDashboardWidgets()` and `$user->getRoleLabel()` behave differently depending on whether `$user` is an `Admin` or `Teacher` object, even though the calling code (`dashboard.php`) is identical. Also: `Student`, `ClassRoom`, `AttendanceRecord` all implement the **same** `create()/update()/delete()` method names from `Crudable`, but each does different SQL |
| **Abstraction**     | `abstract class User` (cannot be instantiated directly — you can never write `new User()`); the `Crudable` interface hides the "how" of CRUD and only exposes "what"; `Security` class hides raw `openssl_*` calls behind simple `encrypt()/decrypt()` |

---

## 6. Security Features Implemented

- **Password hashing**: `password_hash()` / `password_verify()` (bcrypt) — no plain-text passwords anywhere.
- **Prepared statements**: every single SQL query uses PDO parameter binding (`:placeholder`) — no string-concatenated SQL, which prevents SQL injection.
- **Data encryption at rest**: student phone numbers are encrypted with AES-256-CBC (`Security::encrypt()`) before being written to the database, and only decrypted (`Security::decrypt()`) for an authenticated user viewing the record.
- **CSRF protection**: every form (login, add/edit/delete) carries a per-session CSRF token that is verified before any write operation.
- **Input validation**: the `Validator` class checks required fields, email format, minimum length, numeric fields, and valid dates before anything touches the database.
- **Output escaping**: the `e()` helper runs `htmlspecialchars()` on every piece of user data before it's echoed, preventing XSS.
- **Role-based access control**: `Auth::requireLogin()` and `Auth::requireAdmin()` guard every page; teachers can only mark/view attendance for classes assigned to them.

---

## 7. Database Design (3NF)

- **users** (id PK) — admins & teachers
- **students** (id PK) — student master data
- **classes** (id PK, teacher_id FK → users) — one class has exactly one teacher
- **enrollments** (id PK, student_id FK, class_id FK) — resolves the
  many-to-many relationship between students and classes
- **attendance** (id PK, student_id FK, class_id FK, recorded_by FK → users)
  — one row per student/class/date, enforced by a UNIQUE constraint so a
  student can't be marked twice for the same class on the same day

All repeating groups are removed, every non-key column depends only on
its table's primary key, and foreign keys enforce referential integrity —
satisfying 1NF, 2NF and 3NF.

---

## 8. Navigation / UX Notes

- Every add/edit/manage page has a **"Back"** button that returns to the
  relevant list page (Students, Classes, Teachers, Dashboard) — no dead ends.
- All form fields that should be editable are active (`enabled`); fields
  a non-admin isn't allowed to change (e.g. class details for a teacher)
  are shown but disabled, so nothing is hidden or confusing during a demo.
- Delete buttons ask for confirmation (`confirm-delete` in `script.js`)
  before anything is removed.
- Reports page has a **Print** button for a clean, printable report view.
