# Call Center CRM — Installation Guide

## Requirements

| Component | Minimum Version |
|-----------|----------------|
| PHP | 8.2+ |
| MySQL | 8.0+ |
| Apache | 2.4+ with mod_rewrite |
| Composer | 2.x |

## Step-by-Step Setup

### 1. Place project files

Copy the `call_center/` folder to your Apache `DocumentRoot` or a virtual host directory.
Example: `C:\xampp\htdocs\call_center\`

### 2. Install Composer dependencies

```bash
cd call_center
composer install --no-dev --optimize-autoloader
```

### 3. Configure environment

Copy `.env.example` to `.env` and edit:

```
APP_URL=http://localhost/call_center/public

DB_HOST=127.0.0.1
DB_DATABASE=call_center
DB_USERNAME=root
DB_PASSWORD=your_password
```

> If the app is at the domain root (e.g. `http://crm.local`), set `APP_URL=http://crm.local`

### 4. Create the database

Import the schema:
```bash
mysql -u root -p < database/schema.sql
```
Or via phpMyAdmin: Import → select `database/schema.sql`.

### 5. Seed the admin user

```bash
php tools/setup.php
```

Default credentials:
- **Email:** admin@callcenter.com
- **Password:** Admin@1234

**Change the password immediately after first login.**

### 6. Enable Apache mod_rewrite

Ensure `AllowOverride All` is set for your directory in `httpd.conf`:

```apache
<Directory "C:/xampp/htdocs/call_center">
    AllowOverride All
</Directory>
```

Restart Apache.

### 7. Set upload permissions

Ensure Apache can write to:
```
public/assets/uploads/proposals/
public/assets/uploads/imports/
```

On Linux/Mac:
```bash
chmod -R 775 public/assets/uploads/
chown -R www-data:www-data public/assets/uploads/
```

### 8. Open the app

Navigate to: **http://localhost/call_center/public**

---

## Default Login

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@callcenter.com | Admin@1234 |

---

## Excel Import Format

The app auto-detects columns by header name (case-insensitive). Supported headers:

| Field | Accepted Column Names |
|-------|-----------------------|
| Company Name | company, company name, business name |
| Contact | contact, contact name, person |
| Email | email, e-mail, mail |
| Phone | phone, tel, telephone, mobile |
| Website | website, web, url |
| City | city, town |
| Country | country |
| Category | category, industry, sector |
| Notes | notes, remarks, comments |

---

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_URL` | — | Full URL to the `public/` directory |
| `APP_DEBUG` | `false` | Show PHP errors (set `true` only in dev) |
| `DB_HOST` | `127.0.0.1` | MySQL host |
| `DB_DATABASE` | `call_center` | Database name |
| `DB_USERNAME` | `root` | MySQL username |
| `DB_PASSWORD` | — | MySQL password |
| `COMMISSION_RATE` | `10` | Commission % applied to approved deals |
| `UPLOAD_MAX_SIZE` | `10485760` | Max upload in bytes (default 10MB) |
