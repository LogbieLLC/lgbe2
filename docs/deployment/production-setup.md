# Production Setup

This document provides a guide for setting up the LGBE2 application in a production environment.

## Server Requirements

### Hardware Requirements

- **CPU**: Minimum 2 cores, recommended 4+ cores
- **RAM**: Minimum 4GB, recommended 8GB+
- **Storage**: Minimum 20GB SSD, recommended 40GB+ SSD
- **Network**: Reliable internet connection with sufficient bandwidth

### Software Requirements

- **Operating System**: Ubuntu 20.04 LTS or newer (recommended)
- **Web Server**: Nginx (recommended) or Apache
- **PHP**: Version 8.1 or newer
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Node.js**: Version 16.x or newer (for frontend assets)
- **Composer**: Version 2.x
- **Git**: For deployment

## Deployment Process

### 1. Clone the Repository

```bash
git clone https://github.com/your-organization/lgbe2.git /var/www/lgbe2
cd /var/www/lgbe2
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies and build assets
npm install
npm run build
```

### 3. Set Up Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit the `.env` file to set production values:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=lgbe2
DB_USERNAME=your-db-username
DB_PASSWORD=your-db-password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your-mail-username
MAIL_PASSWORD=your-mail-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-aws-key
AWS_SECRET_ACCESS_KEY=your-aws-secret
AWS_DEFAULT_REGION=your-aws-region
AWS_BUCKET=your-s3-bucket
```

### 4. Set Up the Database

```bash
# Run migrations
php artisan migrate --force

# Seed the database (if needed)
php artisan db:seed --force
```

### 5. Optimize the Application

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize class autoloader
composer dump-autoload --optimize
```

### 6. Set Proper Permissions

```bash
# Set ownership
chown -R www-data:www-data /var/www/lgbe2

# Set directory permissions
find /var/www/lgbe2 -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/lgbe2 -type f -exec chmod 644 {} \;

# Set storage and bootstrap cache permissions
chmod -R 775 /var/www/lgbe2/storage /var/www/lgbe2/bootstrap/cache
```

## Web Server Setup

### Nginx Configuration

Create a new Nginx server block configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384;
    
    root /var/www/lgbe2/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
    
    client_max_body_size 20M;
    
    gzip on;
    gzip_comp_level 5;
    gzip_min_length 256;
    gzip_proxied any;
    gzip_vary on;
    gzip_types
        application/atom+xml
        application/javascript
        application/json
        application/ld+json
        application/manifest+json
        application/rss+xml
        application/vnd.geo+json
        application/vnd.ms-fontobject
        application/x-font-ttf
        application/x-web-app-manifest+json
        application/xhtml+xml
        application/xml
        font/opentype
        image/bmp
        image/svg+xml
        image/x-icon
        text/cache-manifest
        text/css
        text/plain
        text/vcard
        text/vnd.rim.location.xloc
        text/vtt
        text/x-component
        text/x-cross-domain-policy;
}
```

Save this configuration to `/etc/nginx/sites-available/lgbe2` and enable it:

```bash
ln -s /etc/nginx/sites-available/lgbe2 /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

### Apache Configuration

If using Apache, create a virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    Redirect permanent / https://your-domain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    
    DocumentRoot /var/www/lgbe2/public
    
    <Directory /var/www/lgbe2/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/lgbe2-error.log
    CustomLog ${APACHE_LOG_DIR}/lgbe2-access.log combined
    
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/your-domain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/your-domain.com/privkey.pem
    
    <FilesMatch "\.(cgi|shtml|phtml|php)$">
        SSLOptions +StdEnvVars
    </FilesMatch>
    
    <Directory /usr/lib/cgi-bin>
        SSLOptions +StdEnvVars
    </Directory>
    
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType image/jpg "access plus 1 month"
        ExpiresByType image/jpeg "access plus 1 month"
        ExpiresByType image/gif "access plus 1 month"
        ExpiresByType image/png "access plus 1 month"
        ExpiresByType text/css "access plus 1 month"
        ExpiresByType application/javascript "access plus 1 month"
    </IfModule>
    
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/x-javascript application/json
    </IfModule>
</VirtualHost>
```

Save this configuration to `/etc/apache2/sites-available/lgbe2.conf` and enable it:

```bash
a2ensite lgbe2.conf
a2enmod ssl rewrite expires deflate
systemctl restart apache2
```

## Database Setup

### MySQL/MariaDB Setup

1. Install MySQL or MariaDB:

```bash
# For MySQL
apt-get update
apt-get install mysql-server

# For MariaDB
apt-get update
apt-get install mariadb-server
```

2. Secure the installation:

```bash
mysql_secure_installation
```

3. Create a database and user:

```sql
CREATE DATABASE lgbe2;
CREATE USER 'lgbe2user'@'localhost' IDENTIFIED BY 'your-secure-password';
GRANT ALL PRIVILEGES ON lgbe2.* TO 'lgbe2user'@'localhost';
FLUSH PRIVILEGES;
```

4. Configure MySQL for production:

Edit `/etc/mysql/mysql.conf.d/mysqld.cnf` (MySQL) or `/etc/mysql/mariadb.conf.d/50-server.cnf` (MariaDB):

```ini
[mysqld]
# Basic settings
max_connections = 100
max_allowed_packet = 16M
thread_cache_size = 8
query_cache_size = 32M
query_cache_limit = 2M

# InnoDB settings
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
```

Restart MySQL/MariaDB:

```bash
systemctl restart mysql
```

## SSL/TLS Configuration

### Let's Encrypt SSL Certificate

1. Install Certbot:

```bash
apt-get update
apt-get install certbot python3-certbot-nginx  # For Nginx
# OR
apt-get install certbot python3-certbot-apache  # For Apache
```

2. Obtain and install a certificate:

```bash
# For Nginx
certbot --nginx -d your-domain.com -d www.your-domain.com

# For Apache
certbot --apache -d your-domain.com -d www.your-domain.com
```

3. Set up automatic renewal:

```bash
echo "0 3 * * * /usr/bin/certbot renew --quiet" | sudo tee -a /etc/crontab
```

## Security Considerations

### Firewall Configuration

Set up a firewall to restrict access:

```bash
# Install UFW
apt-get install ufw

# Set default policies
ufw default deny incoming
ufw default allow outgoing

# Allow SSH, HTTP, and HTTPS
ufw allow ssh
ufw allow http
ufw allow https

# Enable the firewall
ufw enable
```

### PHP Configuration

Optimize PHP for security by editing `/etc/php/8.1/fpm/php.ini`:

```ini
; Disable potentially dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source

; Hide PHP version
expose_php = Off

; Set appropriate limits
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 60
max_input_time = 60

; Enable OPcache
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.enable_cli=1
```

Restart PHP-FPM:

```bash
systemctl restart php8.1-fpm
```

### Regular Updates

Set up automatic security updates:

```bash
apt-get install unattended-upgrades
dpkg-reconfigure -plow unattended-upgrades
```

### Backup Strategy

Implement a regular backup strategy:

```bash
# Install backup tools
apt-get install rsync

# Create a backup script
cat > /usr/local/bin/backup-lgbe2.sh << 'EOF'
#!/bin/bash
TIMESTAMP=$(date +"%Y%m%d-%H%M%S")
BACKUP_DIR="/var/backups/lgbe2"
MYSQL_USER="lgbe2user"
MYSQL_PASSWORD="your-secure-password"
MYSQL_DATABASE="lgbe2"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump --user=$MYSQL_USER --password=$MYSQL_PASSWORD $MYSQL_DATABASE | gzip > $BACKUP_DIR/database-$TIMESTAMP.sql.gz

# Backup application files
rsync -avz --exclude 'node_modules' --exclude 'vendor' --exclude '.git' /var/www/lgbe2/ $BACKUP_DIR/files-$TIMESTAMP/

# Remove backups older than 7 days
find $BACKUP_DIR -type f -name "database-*.sql.gz" -mtime +7 -delete
find $BACKUP_DIR -type d -name "files-*" -mtime +7 -exec rm -rf {} \;
EOF

# Make the script executable
chmod +x /usr/local/bin/backup-lgbe2.sh

# Add to crontab to run daily
echo "0 2 * * * /usr/local/bin/backup-lgbe2.sh" | sudo tee -a /etc/crontab
```

## Monitoring Setup

Install and configure a basic monitoring solution:

```bash
# Install monitoring tools
apt-get install htop iotop sysstat

# Enable system statistics collection
systemctl enable sysstat
systemctl start sysstat

# Install and configure Logwatch for log monitoring
apt-get install logwatch
echo "/usr/sbin/logwatch --output mail --mailto your-email@example.com --detail high" | sudo tee -a /etc/cron.daily/00logwatch
```

For more comprehensive monitoring, consider setting up:

- Prometheus + Grafana for metrics
- ELK Stack (Elasticsearch, Logstash, Kibana) for log management
- Uptime monitoring with services like UptimeRobot or Pingdom
