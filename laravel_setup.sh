#!/bin/bash

# Exit on any error to ensure robust execution
set -e

# Update the system
apt update
apt upgrade -y

# Install dependencies for adding the Ondrej PHP PPA
apt install -y software-properties-common ca-certificates apt-transport-https

# Add Ondrej PHP PPA for PHP 8.4
add-apt-repository ppa:ondrej/php -y
apt update

# Install core components and PHP extensions required for Laravel
apt install -y php8.4 php8.4-fpm nginx mysql-server nodejs \
    php8.4-bcmath php8.4-curl php8.4-mbstring php8.4-mysql \
    php8.4-xml php8.4-zip openssl

# Install Composer globally
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure NGINX to work with PHP-FPM
cat <<'EOL' > /etc/nginx/sites-available/default
server {
    listen 80;
    root /var/www/html;
    index index.php index.html;
    location / {
        try_files $uri $uri/ /index.php?$args;
    }
    location ~ \.php$ {
        include /etc/nginx/fastcgi_params;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
EOL

# Enable the NGINX site configuration
rm -f /etc/nginx/sites-enabled/default
ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Enable and start services
systemctl enable nginx
systemctl start nginx
systemctl enable php8.4-fpm
systemctl start php8.4-fpm
systemctl enable mysql
systemctl start mysql

# Create a test PHP file to verify the setup
echo "<?php phpinfo(); ?>" > /var/www/html/info.php

# Provide user feedback
echo "Setup complete. You can test PHP by accessing http://your_server_ip/info.php"
echo "Remember to secure MySQL by running sudo mysql_secure_installation"
