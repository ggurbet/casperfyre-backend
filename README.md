<p>
	<img src="https://api.casperfyre.com/logo.png" width="300">
</p>

# CasperFYRE API

Dispensory API interface for Casper mainnet. This is the backend API repo for this project. To see the frontend repo, visit https://github.com/ledgerleapllc/casperfyre-frontend

## Dependencies

The system will check for the existence of the following softwares that are required to run a full API server.

```
 1. Ubuntu 18+
 2. Apache2
 3. PHP 7+ {bcmath,bz2,intl,gd,mbstring,mysql,zip,common,curl,xml,gmp}
 4. Mysql 5+
 5. cURL
 6. Composer 2+
```

## Setup

Before doing anything else, make sure you're up to date.

```bash
sudo apt-get update
```

### Software

We generally would use the latest version of Ubuntu for testing installs. Example hosting server: AWS ec2 t2 medium with at least 10Gb SSD. Setup the repo according to our VHOST path using the instruction below. Note, the actual VHOST path in this case would be set to **/var/www/casperfyre-api/public**

### Database

You will first need to create the database itself manually, and set user/password for your DB.

e.g. 

```bash
sudo apt -y install mysql-server
```

```sql
mysql> create database casperfyre_db
mysql> exit
````

Then, you can install basic prerequisites, navigate to http root, and run **setup.sh** to run interactive setup script.

```bash
sudo apt -y install apache2
sudo apt -y install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install -y php8.1
sudo apt-get install -y php8.1-{bcmath,bz2,intl,gd,mbstring,mysql,zip,common,curl,xml}
cd /var/www/
git clone https://github.com/ledgerleapllc/casperfyre-backend casperfyre-api
cd casperfyre-api
./setup.sh
```

If you want to setup the software manually, or if something goes wrong with the setup script, follow these steps.

```bash
sudo apt -y install curl
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl
sudo service apache2 restart
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"
composer install
composer update
cp .env.example .env
RANDOM_ENTROPY=$(head -c 64 < /dev/urandom)
CRON_TOKEN=$(echo $RANDOM_ENTROPY | md5sum | cut -d' ' -f1)
(crontab -l 2>>/dev/null; echo "* * * * * curl   -s http://127.0.0.1/cron/schedule         -H 'Authorization: token $CRON_TOKEN' >> dev/null 2>&1") | crontab -
(crontab -l 2>>/dev/null; echo "* * * * * curl   -s http://127.0.0.1/cron/orders           -H 'Authorization: token $CRON_TOKEN' >> dev/null 2>&1") | crontab -
(crontab -l 2>>/dev/null; echo "*/5 * * * * curl -s http://127.0.0.1/cron/verify-orders    -H 'Authorization: token $CRON_TOKEN' >> dev/null 2>&1") | crontab -
(crontab -l 2>>/dev/null; echo "*/2 * * * * curl -s http://127.0.0.1/cron/refresh-balances -H 'Authorization: token $CRON_TOKEN' >> dev/null 2>&1") | crontab -
(crontab -l 2>>/dev/null; echo "2 * * * * curl   -s http://127.0.0.1/cron/garbage          -H 'Authorization: token $CRON_TOKEN' >> dev/null 2>&1") | crontab -
```

### VHOST

For this example, using an Ubuntu 20 Ec2 instance, our http vhost would look something like this

```
<VirtualHost *:80>
  ServerName api.casperfyre.com
  DocumentRoot /var/www/capsperfyre-api/public
  <Directory /var/www/capsperfyre-api/public>
    AllowOverride All
    Require all granted
    Options -MultiViews

    RewriteEngine On

    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Headers "*"
    Header set Access-Control-Allow-Methods "POST, GET, OPTIONS, DELETE, PUT"
  </Directory>

  ErrorDocument 403 /403.php
  ErrorDocument 404 /404.php
  ErrorDocument 500 /500.php

  ErrorLog ${APACHE_LOG_DIR}/error.log
  CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

The first time pinging the server will build the tables inside the given named database and create an admin account.

Find admin credentials in apache error log. Example output:

````
[CasperFYRE 2022-04-05T12:28:46-04:00] - Created admin
[CasperFYRE 2022-04-05T12:28:46-04:00] - Email: "email"
[CasperFYRE 2022-04-05T12:28:46-04:00] - Password: "password"
````

You can also use the apache error log to explore any/all API calls resulting in anything other than a success 200 code.

### Finish Environment

The setup script does not contain interactive input pertaining to the webmaster email. You must setup an email server and provide an email username, password, port, host, etc; Or a relay API key like Sendgrid, in order to make the webmaster email scheduler function. This is required for receiving 2fa codes, getting registration confirmation codes, password reset links, and other similar functions.

```bash
cat .env
```

Adjust your .env configuration variables to fit your instance. 

## Usage Guide

### Start here

aaaaaaa

### Other notes

These features were scoped and determined to be the essential features needed for CasperFYRE. Email any questions to team@ledgerleap.com.

### Testing

We use PHPUnit for unit testing of the portal's critical functionality. In order to run the test suite, you will need to build composer dependencies ensuring a proper backend build. Run **composer run-script --test** to run the unit tests and see output on the CLI. Run this command at the root of the repo directory.

```bash
composer run-script test
```

### Documentation

We use PHPDoc to generate new API documentation each time there are changes to the API. You can generate your own local documentation by running:

```bash
composer run-script generate-docs
```

You will find the newly generated documentation at /docs/index.html
