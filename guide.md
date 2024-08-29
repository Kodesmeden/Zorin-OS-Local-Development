
# Ubuntu Local Development Guide

## Forberedelser

### Skift til root-bruger
```bash
sudo -s
```

### Konfigurer `sudo` rettigheder
```bash
visudo
```
Tilføj følgende linje for at tillade `kodesmeden` brugeren at bruge `sudo` uden adgangskode:
```bash
kodesmeden ALL=(ALL) NOPASSWD: ALL
```

## Installation af Nginx, PHP & Database

### Opdater systemet og installer nødvendige pakker
```bash
apt update && apt upgrade -y
apt install nginx -y
systemctl enable nginx
apt install software-properties-common -y
add-apt-repository ppa:ondrej/php -y
apt update
apt install php-cli mysql-server redis-server -y
systemctl enable mysql
systemctl enable redis-server
```

### Juster Nginx konfiguration
```bash
nano /etc/nginx/nginx.conf
```
Tilføj følgende øverst for at tillade Nginx at distribuere CPU-belastning:
```nginx
worker_cpu_affinity auto;
```
Ændr `worker_connections` til 1024 (eller find grænsen med "ulimit -n"):
```nginx
events {
    worker_connections 1024;
}
```

## Installation af systemmoduler
```bash
apt install wget curl git-all zip unzip imagemagick webp dnsutils bind9-utils memcached postfix libsasl2-modules mailutils php-dev php-pear -y
```

## Installation af NVM & NodeJS
```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh | bash
export NVM_DIR="$([ -z "${XDG_CONFIG_HOME-}" ] && printf %s "${HOME}/.nvm" || printf %s "${XDG_CONFIG_HOME}/nvm")"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
nvm install --lts
curl -fsSL https://deb.nodesource.com/setup_22.x -o nodesource_setup.sh
sudo -E bash nodesource_setup.sh
apt install nodejs -y
node -v
```

## Installation af Composer
```bash
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
php /tmp/composer-setup.php --install-dir=/usr/bin --filename=composer
```

## Tilføj PHP repository
```bash
add-apt-repository ppa:ondrej/php -y
add-apt-repository ppa:ondrej/nginx -y
```

## Installation af PHP 8.2 og PHP 8.3
### PHP 8.2:
```bash
apt install php8.2 php8.2-fpm php8.2-common php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-gd php8.2-redis php8.2-mongodb php8.2-imagick php8.2-zip php8.2-memcached php8.2-dev -y
systemctl enable php8.2-fpm
```

### PHP 8.3:
```bash
apt install php8.3 php8.3-fpm php8.3-common php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-gd php8.3-redis php8.3-mongodb php8.3-imagick php8.3-zip php8.3-memcached php8.3-dev -y
systemctl enable php8.3-fpm
```

## Installation af Xdebug og ændring af PHP-konfigurationer

### Installér Xdebug
```bash
pecl install xdebug
```

### Ændr PHP 8.2 konfiguration
```bash
nano /etc/php/8.2/fpm/php.ini
```
Rediger følgende værdier:
```ini
max_execution_time = 600
max_input_time = 600
max_input_vars = 5000
memory_limit = 2048M
post_max_size = 4096M
upload_max_filesize = 4096M
default_socket_timeout = 600
```
Tilføj følgende nederst:
```ini
[xdebug]
zend_extension=/usr/lib/php/20230831/xdebug.so
xdebug.mode=debug
```
Rediger bruger og ejer i `www.conf`:
```bash
nano /etc/php/8.2/fpm/pool.d/www.conf
```
Rediger følgende værdier:
```ini
user = kodesmeden
listen.owner = kodesmeden
```

### Ændr PHP 8.3 konfiguration
Samme trin som PHP 8.2, bare med stien til PHP 8.3.

## Opsætning af Postfix
```bash
nano /etc/postfix/main.cf
```
Indsæt eller rediger følgende variabler:
```ini
smtp_sasl_auth_enable = yes
smtp_sasl_password_maps = hash:/etc/postfix/sasl_passwd
smtp_sasl_security_options = noanonymous
smtp_sasl_tls_security_options = noanonymous
smtp_tls_security_level = encrypt
header_size_limit = 4096000
relayhost = [smtp.zeptomail.eu]:587
mailbox_size_limit = 512000000
```

Konfigurer SMTP-brugernavn og adgangskode:
```bash
nano /etc/postfix/sasl_passwd
```
Ændr følgende linje til at matche din SMTP-vært:
```bash
[smtp.zeptomail.eu]:587 user:password
```
Sæt de nødvendige rettigheder og genstart Postfix:
```bash
chmod 600 /etc/postfix/sasl_passwd
postmap /etc/postfix/sasl_passwd
systemctl enable postfix.service
service postfix restart
```

## Installation af MariaDB & phpMyAdmin
```bash
apt install mysql-server mysql-client-core-8.0 -y
cd /tmp
wget https://files.phpmyadmin.net/phpMyAdmin/5.2.1/phpMyAdmin-5.2.1-all-languages.zip
unzip phpMyAdmin-5.2.1-all-languages.zip
mv phpMyAdmin-5.2.1-all-languages /usr/share/phpmyadmin
cp /usr/share/phpmyadmin/config.sample.inc.php /usr/share/phpmyadmin/config.inc.php
rm /tmp/phpMyAdmin-5.2.1-all-languages.zip
mkdir -p /usr/share/phpmyadmin/tmp/
chown -R kodesmeden:www-data /usr/share/phpmyadmin/
```
Konfigurer phpMyAdmin:
```bash
nano /usr/share/phpmyadmin/config.inc.php
```
Rediger følgende linjer:
```php
/* Authentication type */
$cfg['Servers'][$i]['auth_type'] = 'config';
$cfg['Servers'][$i]['user'] = 'root';
$cfg['Servers'][$i]['password'] = '';
/* Server parameters */
$cfg['Servers'][$i]['host'] = '127.0.0.1';
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['AllowNoPassword'] = true;
$cfg['Servers'][$i]['hide_db'] = '(information_schema|mysql|performance_schema|sys|phpmyadmin)';
```
Sæt MariaDB root brugerens adgangskode:
```bash
mysql -u root -p
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
exit;
mysql -u root -p < /usr/share/phpmyadmin/sql/create_tables.sql
```

## Opsætning af virtuelle hosts
Opret Nginx konfiguration for phpMyAdmin:
```bash
nano /etc/nginx/sites-available/pma.test
```
Indsæt følgende:
```nginx
server {
    listen 80;
    server_name pma.test;
    root /usr/share/phpmyadmin;

    index index.php;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* ^/(robots\.txt|favicon\.ico)$ {
        log_not_found off;
        access_log off;
    }

    location ~ /\.(git|well-known|vscode) {
        deny all;
    }
}
```
Aktivér konfigurationen:
```bash
ln -s /etc/nginx/sites-available/pma.test /etc/nginx/sites-enabled/
```

Konfigurer PHP 8.2 for phpMyAdmin:
```bash
nano /etc/php/8.2/fpm/pool.d/pma.test.conf
```
Indsæt følgende:
```ini
[pma.test]
user = kodesmeden
group = www-data
listen = /run/php/php8.2-pma.sock
listen.owner = kodesmeden
listen.group = www-data
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
```

## Opsætning af Git
```bash
git config --global user.name "Kodesmeden"
git config --global user.email "info@kodesmeden.dk"
```

## Konfiguration af Bash Profile
```bash
nano /home/kodesmeden/.bashrc
```
Indsæt følgende:
```bash
if [ -f "$HOME/.bash_profile" ]; then
    source "$HOME/.bash_profile"
fi
```
Ændr umask og ssh tilladelser:
```bash
umask 002
chmod -R 0600 /home/kodesmeden/.ssh/*
```

## Opsætning af udviklingsmiljø
Opret Nginx konfiguration for udviklingsmiljø:
```bash
nano /etc/nginx/sites-available/dev.test
```
Indsæt følgende:
```nginx
server {
    listen 80;
    server_name dev.test;
    root /var/www/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* ^/(robots\.txt|favicon\.ico)$ {
        log_not_found off;
        access_log off;
    }

    location ~ /\.(git|well-known|vscode) {
        deny all;
    }
}
```
Aktivér konfigurationen:
```bash
ln -s /etc/nginx/sites-available/dev.test /etc/nginx/sites-enabled/
```

Konfigurer PHP 8.3 for udviklingsmiljøet:
```bash
nano /etc/php/8.3/fpm/pool.d/dev.test.conf
```
Indsæt følgende:
```ini
[dev.test]
user = kodesmeden
group = www-data
listen = /run/php/php8.3-dev.sock
listen.owner = kodesmeden
listen.group = www-data
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
```

## Ændr hosts fil
```bash
nano /etc/hosts 
```
Indsæt følgende:
```plaintext
127.0.0.1 localhost
127.0.0.1 pma.test
127.0.0.1 dev.test
```

## Genstart services
```bash
service php8.2-fpm restart
service php8.3-fpm restart
service mysql restart
systemctl restart systemd-resolved
systemctl restart nginx
systemctl restart NetworkManager
```

## Opdatering af systemet
```bash
composer self-update
npm install -g npm@latest
apt update
apt -y upgrade
apt -y auto-remove
```

## Rettelse af tilladelser
```bash
systemctl restart systemd-resolved
chown -R kodesmeden:www-data /usr/share/phpmyadmin
chown -R kodesmeden:www-data /var/www
find /var/www -type d -exec chmod 755 {} \;
find /var/www -type f -exec chmod 644 {} \;
find /var/www -type d -exec chmod 2755 {} +
```

## Kloning af udviklingsmiljø (som bruger, ikke root)
```bash
su - kodesmeden
```
Tilføj `kodesmeden` til `www-data` gruppen:
```bash
sudo usermod -aG www-data kodesmeden
newgrp www-data
```
Klon udviklingsmiljøet:
```bash
cd /var/www
rm -rf *
git clone git@github.com:Kodesmeden/Zorin-OS-Local-Development.git .
cp .env.example .env
composer update
php artisan key:generate
php artisan migrate --force
npm install vite
npm run build
```

## Opret skrivebordsgenvej
```bash
touch ~/Skrivebord/open_www.desktop
nano ~/Skrivebord/open_www.desktop
```
Indsæt følgende:
```ini
[Desktop Entry]
Name=Development Platform
Comment=Open terminal in /var/www
Exec=gnome-terminal --working-directory=/var/www
Icon=utilities-terminal
Terminal=false
Type=Application
```
