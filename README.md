# PayProof: School Fee Receipt Authentication & Verification System

A web-based system that lets bursary officers **authenticate, verify and audit school fee receipts**, built to stop forged and reused receipts.

> Built with PHP, MySQL, HTML/CSS. Developed locally on WAMP/XAMPP.

## The Problem

Schools issue fee receipts from a central portal, but forged, altered or reused receipts still circulate. Manual checking is slow and error-prone, and there is usually no audit trail showing which receipts were checked, reused or faked.

## Objectives

- **Authenticate receipts:** confirm a receipt matches a real payment record.
- **Prevent reuse:** lock a receipt once it has been verified.
- **Role-based access:** only bursary officers and admins can verify.
- **Audit logging:** record every check with user, action, IP and timestamp.
- **Easy integration:** can be linked from the existing school portal via a "Verify Receipt" button (see `public/portal_mock.php`).

## Features

- Login with role-based access control (`admin`, `bursary`)
- Receipt lookup by unique token
- SHA-256 integrity check that detects tampered records
- Status workflow: `unverified` -> `verified` / `rejected` (locked after processing), plus `reused` flagging
- Audit log page (last 200 actions)
- Admin user list
- Demo "school portal" page showing where the verify button would sit

## How Verification Works

1. Officer enters a receipt token.
2. The system loads the receipt, its payment and the student.
3. It recomputes `SHA-256(token | reference | amount | matric_no | APP_SECRET_KEY)` and compares it with the stored hash using a timing-safe comparison.
4. Result:
   - **Hash mismatch:** flagged as tampered or fake.
   - **Already verified:** flagged as possible reuse.
   - **Valid and unverified:** officer can open the receipt and **Verify (Lock)** or **Reject**.
5. Every step is written to `verification_logs`.

## Security Considerations

- Passwords stored with `password_hash()` / checked with `password_verify()`
- All SQL uses PDO **prepared statements** (emulation off)
- **CSRF tokens** on every state-changing form, validated with `hash_equals()`
- Output escaped with `htmlspecialchars()` to mitigate XSS
- Session cookies: `HttpOnly`, `SameSite=Lax`, `Secure` over HTTPS, strict mode, ID regenerated on login
- Server-side role checks (`require_role`) on admin pages
- Secret key kept in a git-ignored config file, never in the repo
- Status update uses `WHERE status='unverified'` so a receipt can't be verified twice

## Tech Stack

| Layer | Technology |
| --- | --- |
| Frontend | HTML, CSS |
| Backend | PHP 8+ |
| Database | MySQL / MariaDB |
| Server | Apache (WAMP / XAMPP) |
| Security | SHA-256, bcrypt (via `password_hash`), CSRF tokens, RBAC |

## Project Structure

```
payproof/
├── assets/style.css
├── config/
│   ├── config.example.php   # copy to config.php
│   └── db.php
├── database/
│   ├── schema.sql
│   ├── seed.php             # demo users + receipts (CLI)
│   └── reset_password.php   # reset a user's password (CLI)
├── includes/                # auth, csrf, session, header/footer
├── public/                  # web root pages
├── docs/screenshots/
└── README.md
```

## Installation

1. **Clone** into your server folder (e.g. `htdocs/` or `www/`):
   ```bash
   git clone https://github.com/<Goodluck-oladimeji>/payproof.git
   ```
2. **Create the database:** import `database/schema.sql` (phpMyAdmin or `mysql < database/schema.sql`).
3. **Configure:**
   ```bash
   cp config/config.example.php config/config.php
   ```
   Edit `config/config.php`: set your DB credentials, `APP_URL`, and a long random `APP_SECRET_KEY`
   (`php -r "echo bin2hex(random_bytes(32));"`).
4. **Seed demo data** (prints random demo passwords and sample receipt tokens):
   ```bash
   php database/seed.php
   ```
5. **Open** `http://localhost/payproof/public/login.php` and sign in with the printed credentials.

The seed also creates one deliberately tampered receipt so you can see the "hash mismatch" detection.

Forgot a password? `php database/reset_password.php <username>` sets a new random one.

## Screenshots

Add images to `docs/screenshots/` and link them here:

| Login | Verify | Audit Logs |
| --- | --- | --- |
| ![Login](docs/screenshots/login.png) | ![Verify](docs/screenshots/verify.png) | ![Logs](docs/screenshots/logs.png) |

## Known Limitations / Future Improvements

- Login rate limiting and account lockout
- Admin UI to create/disable users and reset passwords
- HMAC (`hash_hmac`) instead of concatenating the secret into a plain SHA-256
- Receipt QR codes and PDF generation
- Reports and CSV export of audit logs
- Real integration with a school portal (API or signed links)
- Automated tests and security headers (CSP, HSTS)

## Author

**Goodluck**, Computer Science / Cybersecurity

## License

MIT, see [LICENSE](LICENSE).
