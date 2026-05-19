# Ubuntu 22.04 LTS — Complete Server Deployment Guide
## Call Center CRM — PHP 8.2 / MySQL 8.0 / Apache2

---

## 1. System Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| OS | Ubuntu 22.04 LTS | Ubuntu 22.04 LTS |
| CPU | 2 vCPU | 4 vCPU |
| RAM | 2 GB | 4 GB |
| Disk | 20 GB SSD | 50 GB SSD |
| PHP | 8.2 | 8.2 |
| MySQL | 8.0 | 8.0 |
| Apache | 2.4 | 2.4 |

---

## 2. Initial Server Setup

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Set hostname
sudo hostnamectl set-hostname callcenter-crm

# Set timezone
sudo timedatectl set-timezone Europe/Athens

# Install essential tools
sudo apt install -y curl wget git unzip software-properties-common apt-transport-https ca-certificates
```

---

## 3. Install PHP 8.2 + Extensions

```bash
# Add Ondřej Surý PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.2 and required extensions
sudo apt install -y \
    php8.2 \
    php8.2-cli \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-gd \
    php8.2-intl \
    php8.2-bcmath \
    php8.2-opcache \
    php8.2-json

# Verify installation
php8.2 --version
php8.2 -m | grep -E 'pdo|mysql|mbstring|curl|zip|gd|xml|intl|bcmath|opcache'
```

### Configure PHP-FPM

```bash
# Edit PHP-FPM configuration
sudo nano /etc/php/8.2/fpm/php.ini
```

Key settings to update in `php.ini`:

```ini
; Memory and upload limits
memory_limit = 256M
upload_max_filesize = 32M
post_max_size = 32M
max_execution_time = 120
max_input_time = 120

; Error handling (production)
display_errors = Off
log_errors = On
error_log = /var/log/php/php_errors.log

; Session security
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1

; OPcache
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 60
```

```bash
# Create PHP log directory
sudo mkdir -p /var/log/php
sudo chown www-data:www-data /var/log/php

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
sudo systemctl enable php8.2-fpm
```

---

## 4. Install MySQL 8.0

```bash
# Install MySQL Server
sudo apt install -y mysql-server

# Secure installation
sudo mysql_secure_installation
# - Set root password: YES
# - Remove anonymous users: YES
# - Disallow root login remotely: YES
# - Remove test database: YES
# - Reload privilege tables: YES

# Start and enable MySQL
sudo systemctl start mysql
sudo systemctl enable mysql

# Verify
sudo systemctl status mysql
```

### Create Application Database & User

```bash
sudo mysql -u root -p
```

```sql
-- Create database
CREATE DATABASE callcenter_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated user
CREATE USER 'crm_user'@'localhost' IDENTIFIED BY 'Pkots1!pkots2';

-- Grant privileges
GRANT ALL PRIVILEGES ON callcenter_crm.* TO 'crm_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

EXIT;
```

---

## 5. Install Apache2 + mod_rewrite

```bash
# Install Apache
sudo apt install -y apache2

# Enable required modules
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl
sudo a2enmod proxy_fcgi
sudo a2enconf php8.2-fpm

# Start and enable Apache
sudo systemctl start apache2
sudo systemctl enable apache2

# Verify
apache2 -v
```

---

## 6. Install Composer

```bash
# Download Composer installer
curl -sS https://getcomposer.org/installer -o composer-setup.php

# Verify installer checksum (get latest from https://composer.github.io/pubkeys.html)
HASH=$(curl -sS https://composer.github.io/installer.sig)
php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

# Install globally
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"

# Verify
composer --version
```

---

## 7. Deploy the Application

```bash
# Create web root
sudo mkdir -p /var/www/callcenter

# Option A: Clone from Git
sudo git clone https://your-repo-url.git /var/www/callcenter

# Option B: Upload files manually via SCP/SFTP
# scp -r ./call_center/* user@server-ip:/var/www/callcenter/

# Set ownership
sudo chown -R www-data:www-data /var/www/callcenter
sudo find /var/www/callcenter -type f -exec chmod 644 {} \;
sudo find /var/www/callcenter -type d -exec chmod 755 {} \;
```

### Install PHP Dependencies

```bash
cd /var/www/callcenter

# Install Composer dependencies (production mode)
sudo -u www-data composer install --no-dev --optimize-autoloader
```

---

## 8. Configure Environment File

```bash
# Copy example env file
sudo cp /var/www/callcenter/.env.example /var/www/callcenter/.env

# Edit environment variables
sudo nano /var/www/callcenter/.env
```

Set the following values:

```dotenv
APP_NAME="Call Center CRM"
APP_URL=https://yourdomain.com/public
APP_ENV=production
APP_DEBUG=false

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=callcenter_crm
DB_USERNAME=crm_user
DB_PASSWORD=STRONG_PASSWORD_HERE

COMMISSION_RATE=10
UPLOAD_MAX_SIZE=10485760
```

```bash
# Secure the .env file
sudo chmod 640 /var/www/callcenter/.env
sudo chown root:www-data /var/www/callcenter/.env
```

---

## 9. Database Setup

```bash
# Import base schema
sudo mysql -u crm_user -p callcenter_crm < /var/www/callcenter/database/schema.sql

# Run V2 migration (new tables)
sudo mysql -u crm_user -p callcenter_crm < /var/www/callcenter/database/migration_v2.sql
```

### Verify tables were created:

```bash
sudo mysql -u crm_user -p callcenter_crm -e "SHOW TABLES;"
```

Expected tables: `users`, `businesses`, `caller_assignments`, `interactions`, `interaction_services`, `deals`, `commissions`, `messages`, `services`, `user_roles`, `projects`, `project_phases`, `project_notes`, `contracts`, `invoices`, `expenses`

---

## 10. File Permissions

```bash
# Upload directories must be writable by www-data
sudo mkdir -p /var/www/callcenter/public/assets/uploads/{imports,proposals,contracts,invoices,receipts}
sudo chown -R www-data:www-data /var/www/callcenter/public/assets/uploads
sudo chmod -R 775 /var/www/callcenter/public/assets/uploads

# Storage/log directories
sudo mkdir -p /var/www/callcenter/storage/logs
sudo chown -R www-data:www-data /var/www/callcenter/storage
sudo chmod -R 775 /var/www/callcenter/storage

# Verify
ls -la /var/www/callcenter/public/assets/uploads/
```

---

## 11. Apache VirtualHost Configuration

```bash
# Create VirtualHost config
sudo nano /etc/apache2/sites-available/callcenter.conf
```

Paste this configuration:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com

    DocumentRoot /var/www/callcenter/public

    <Directory /var/www/callcenter/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # PHP-FPM via Unix socket
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost"
    </FilesMatch>

    # Security headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    # Logging
    ErrorLog ${APACHE_LOG_DIR}/callcenter_error.log
    CustomLog ${APACHE_LOG_DIR}/callcenter_access.log combined
</VirtualHost>
```

```bash
# Enable the site and disable default
sudo a2ensite callcenter.conf
sudo a2dissite 000-default.conf

# Test configuration
sudo apache2ctl configtest

# Reload Apache
sudo systemctl reload apache2
```

---

## 12. SSL with Let's Encrypt (Certbot)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-apache

# Obtain SSL certificate (replace with your domain)
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Follow prompts:
# - Enter email address for renewal notices
# - Agree to terms of service
# - Choose to redirect HTTP to HTTPS (option 2 - recommended)

# Verify auto-renewal is set up
sudo systemctl status certbot.timer
sudo certbot renew --dry-run
```

After Certbot, your VirtualHost for port 443 will be auto-configured. Verify:

```bash
sudo cat /etc/apache2/sites-available/callcenter-le-ssl.conf
```

---

## 13. PHP-FPM Pool Configuration (Performance Tuning)

```bash
sudo nano /etc/php/8.2/fpm/pool.d/callcenter.conf
```

```ini
[callcenter]
user = www-data
group = www-data

listen = /run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

; Process management
pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 8
pm.max_requests = 500

; Logging
slowlog = /var/log/php/php8.2-fpm-slow.log
request_slowlog_timeout = 10s

; Environment
env[HOSTNAME] = $HOSTNAME
env[PATH] = /usr/local/bin:/usr/bin:/bin
env[TMP] = /tmp
env[TMPDIR] = /tmp
env[TEMP] = /tmp

; Limits
php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 32M
php_admin_value[post_max_size] = 32M
```

```bash
sudo systemctl restart php8.2-fpm
```

---

## 14. Firewall (UFW)

```bash
# Enable UFW
sudo ufw enable

# Allow essential ports
sudo ufw allow ssh           # Port 22
sudo ufw allow 'Apache Full' # Ports 80 and 443

# Verify
sudo ufw status verbose
```

---

## 15. Systemd Services

Ensure all services start on boot:

```bash
sudo systemctl enable apache2
sudo systemctl enable php8.2-fpm
sudo systemctl enable mysql

# Check service status
sudo systemctl status apache2
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
```

---

## 16. Cron Jobs

If you add scheduled tasks (e.g., automated reminders, report generation):

```bash
# Edit crontab for www-data user
sudo crontab -u www-data -e
```

Example entries:

```cron
# Send deadline reminders - runs daily at 8:00 AM
0 8 * * * php /var/www/callcenter/tools/deadline_reminders.php >> /var/log/callcenter-cron.log 2>&1

# Database backup - runs daily at 2:00 AM
0 2 * * * mysqldump -u crm_user -pSTRONG_PASSWORD_HERE callcenter_crm | gzip > /var/backups/callcenter_$(date +\%F).sql.gz

# Clean old uploads (imports older than 30 days)
0 3 * * 0 find /var/www/callcenter/public/assets/uploads/imports -mtime +30 -delete
```

---

## 17. Log Management (Logrotate)

```bash
sudo nano /etc/logrotate.d/callcenter
```

```
/var/log/apache2/callcenter_*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    sharedscripts
    postrotate
        systemctl reload apache2 > /dev/null 2>&1
    endscript
}

/var/log/php/php_errors.log {
    daily
    missingok
    rotate 7
    compress
    delaycompress
    notifempty
    create 640 www-data www-data
}
```

---

## 18. Database Backup Script

```bash
sudo nano /usr/local/bin/backup-crm.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/callcenter"
DB_NAME="callcenter_crm"
DB_USER="crm_user"
DB_PASS="STRONG_PASSWORD_HERE"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p "$BACKUP_DIR"

# Dump database
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Keep only last 30 backups
find "$BACKUP_DIR" -name "db_*.sql.gz" -mtime +30 -delete

echo "Backup completed: $BACKUP_DIR/db_$DATE.sql.gz"
```

```bash
sudo chmod +x /usr/local/bin/backup-crm.sh
```

---

## 19. Admin User Setup

After deployment, create the initial admin user:

```bash
cd /var/www/callcenter
sudo -u www-data php tools/hash_password.php
```

Or insert directly into the database:

```sql
-- Replace HASHED_PASSWORD with output of password_hash('yourpassword', PASSWORD_BCRYPT, ['cost' => 12])
INSERT INTO users (name, email, password, role, is_active, created_at)
VALUES ('Administrator', 'admin@yourdomain.com', 'HASHED_PASSWORD', 'admin', 1, NOW());
```

---

## 20. Troubleshooting

### Apache returns 403 Forbidden
```bash
# Check .htaccess exists in public/
ls -la /var/www/callcenter/public/.htaccess

# Check mod_rewrite is enabled
apache2ctl -M | grep rewrite

# Check Directory permissions
sudo chmod 755 /var/www/callcenter
sudo chmod 755 /var/www/callcenter/public
```

### PHP 500 Internal Server Error
```bash
# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check Apache error log
sudo tail -100 /var/log/apache2/callcenter_error.log

# Check PHP error log
sudo tail -100 /var/log/php/php_errors.log

# Temporarily enable errors for debugging
sudo nano /var/www/callcenter/.env
# Set: APP_DEBUG=true
```

### Database Connection Error
```bash
# Test database connection
mysql -u crm_user -p callcenter_crm -e "SELECT 1;"

# Check .env values
cat /var/www/callcenter/.env | grep DB_

# Check MySQL is running
sudo systemctl status mysql
```

### File Upload Issues
```bash
# Check directory permissions
ls -la /var/www/callcenter/public/assets/uploads/

# Fix permissions
sudo chown -R www-data:www-data /var/www/callcenter/public/assets/uploads/
sudo chmod -R 775 /var/www/callcenter/public/assets/uploads/

# Check PHP upload limits
php8.2 -i | grep -E 'upload_max|post_max|memory_limit'
```

### SSL Certificate Renewal
```bash
# Test renewal
sudo certbot renew --dry-run

# Manual renewal
sudo certbot renew

# Check certificate expiry
sudo certbot certificates
```

### Performance Issues
```bash
# Check OPcache status
php8.2 -r "var_dump(opcache_get_status());"

# Check slow query log (enable in MySQL)
sudo mysql -e "SET GLOBAL slow_query_log = 'ON'; SET GLOBAL long_query_time = 1;"

# Check server load
htop
# or
top -u www-data
```

---

## 21. Security Checklist

- [ ] Strong, unique database password
- [ ] `.env` file has `640` permissions and `root:www-data` ownership
- [ ] `APP_DEBUG=false` in production
- [ ] UFW firewall active with only ports 22, 80, 443 open
- [ ] SSL certificate installed and auto-renewed
- [ ] No write permission on source files (only `uploads/` directories)
- [ ] MySQL root login remote access disabled
- [ ] Regular automated backups configured
- [ ] Apache directory listing disabled (`Options -Indexes`)
- [ ] Security headers configured in VirtualHost
- [ ] PHP-FPM running as `www-data` (not root)
- [ ] SSH key-based authentication (disable password auth)

---

## 22. Post-Deployment Verification

```bash
# Test HTTP redirect to HTTPS
curl -I http://yourdomain.com

# Test HTTPS response
curl -I https://yourdomain.com/public

# Test PHP is working
curl https://yourdomain.com/public/auth/login

# Check file uploads directory is accessible (should return 403)
curl -I https://yourdomain.com/public/assets/uploads/

# Verify all services are running
sudo systemctl status apache2 php8.2-fpm mysql
```

---

*Prepared for Call Center CRM — PHP 8.2 MVC Application*
*Ubuntu 22.04 LTS Deployment Guide*
