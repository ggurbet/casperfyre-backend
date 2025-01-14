<p>
	<img src="https://api.casperfyre.com/logo.png" width="300">
</p>

# CasperFYRE API

Dispensory API interface for Casper mainnet. This is the backend API repo for this project. To see the frontend repo, visit https://github.com/ledgerleapllc/casperfyre-frontend

## Dependencies

The system will check for the existence of the following softwares that are required to run a full API server.

```
 1. Ubuntu 20+
 2. Apache2 (Not required for dev build)
 3. PHP 7+ {bcmath,bz2,intl,gd,mbstring,mysql,zip,common,curl,xml,gmp,sqlite3}
 4. Mysql 5+
 5. cURL
 6. Composer 2+
 7. Casper Client 1.4+
```

Ubuntu 20+ and Apache2 are optional and can be switched out for other common platforms, but the built-in interactive setup script will not work in that case. The setup of the operating system and HTTP server software should be handled by the dev. Note, the http document root is **casperfyre-backend/public**

## Setup

We generally would use the latest version of Ubuntu for testing installs. Example hosting server: AWS ec2 t2 medium with at least 10Gb SSD. Before doing anything else, make sure you're up to date.

For a dev build, we are using Gitpod.

**Option 1 -** Use Gitpod Dev deployment. Spin up a new Gitpod workspace and execute the following.

```bash
./setup.sh
apachectl start
```

Gitpod should allow you to forward the traffic from the API to the browser and use the public URL as our API to which we can point a front end.

Since there is no emailer in Dev mode, we can collect email content, 2fa codes, etc. From the logger.

```bash
tail -f /var/log/apache2/error.log
```

**Option 2 -** If you need to setup the software manually, or if something goes wrong with the setup script, or if you plan on deploying a production build, follow these steps.

```bash
sudo apt -y install apache2
sudo apt -y install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install -y php8.1
sudo apt-get install -y php8.1-{bcmath,bz2,intl,gd,mbstring,mysql,zip,common,curl,xml,gmp}
sudo apt -y install curl
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl
sudo service apache2 restart
sudo chown -R www-data:www-data tmp/
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
echo "deb https://repo.casperlabs.io/releases" bionic main | sudo tee -a /etc/apt/sources.list.d/casper.list
curl -O https://repo.casperlabs.io/casper-repo-pubkey.asc
sudo apt-key add casper-repo-pubkey.asc
sudo apt update
sudo apt install casper-client
```

### VHOST

For this example, using an Ubuntu 20 Ec2 instance, our http vhost would look something like this

```
<VirtualHost *:80>
  ServerName api.casperfyre.com
  DocumentRoot /var/www/casperfyre-api/public
  <Directory /var/www/casperfyre-api/public>
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

## Finish Environment

The first time pinging the server will build the tables inside the given named database and create an admin account.

Find admin credentials in the server log. Example output:

````
[CasperFYRE 2022-04-05T12:28:46-04:00] - Created admin
[CasperFYRE 2022-04-05T12:28:46-04:00] - Email: "email"
[CasperFYRE 2022-04-05T12:28:46-04:00] - Password: "password"
````

You can also use the server log to explore any/all API calls resulting in anything other than a success 200 code.

The setup script does not contain interactive input pertaining to the webmaster email. You must setup an email server and provide an email username, password, port, host, etc; Or a relay API key like Sendgrid, in order to make the webmaster email scheduler function. This is required for receiving 2fa codes, getting registration confirmation codes, password reset links, and other similar functions.

```bash
cat .env
```

Adjust your .env configuration variables to fit your instance. 

Finally, you can use the built in PHP server for testing. To spin up the built in PHP server, run

```bash
composer run-script serve-dev
```

## Usage Guide

### Start here

After your admin account and credentials have been created and you have logged in, the first thing to see is the *Applications* tab. This is where new platform users wil appear for registration acceptance. As an admin, you can approve/deny users joining your API interface. The *API Keys* tab will show approved users, named *Keyholders* here. You can manage keyholders, their API keys, wallets, and settings here. The *API Logs* tab show a global view and API request history across all your keyholders. *Wallets* tab shows your keyholder's wallet details globally for management. *Settings* tab is for your admin account settings. You will find email/password/MFA settings here, as well as other admins you have assigned to manage your keyholders, known as *sub-admins*.

As shown in the dashboard settings and documentation, API users can use syntax like the example below to interact with the public facing API.

```bash
curl -X POST https://api.casperfyre.com/v1/dispense \
  -H 'Content-type: application/json' \
  -H 'Authorization: token API_KEY' \
  -d '{ "address": "CSPR_ADDRESS", "amount": 100 }'
```

Or, if you are testing with a Gitpod,

```bash
curl -X POST https://YOUR-GITPOD-URL.gitpod.io/v1/dispense \
  -H 'Content-type: application/json' \
  -H 'Authorization: token API_KEY' \
  -d '{ "address": "CSPR_ADDRESS", "amount": 100 }'
```

This example curl request will dispense 100 token to the specified address. Please note, if you are deploying to your own instance and domain, the base URL will be your own URL that you setup, not **api.casperfyre.com**.

### Other notes

These features were scoped and determined to be the essential features needed for CasperFYRE. Email any questions to team@ledgerleap.com.

### Testing

We use PHPUnit for unit testing of the portal's critical functionality. In order to run the test suite, you will need to build composer dependencies ensuring a proper backend build. Run **composer run-script test** to run the unit tests and see output on the CLI. Run this command at the root of the repo directory.

```bash
composer run-script test
```

### Documentation

We use PHPDoc to generate new API documentation each time there are changes to the API. You can generate your own local documentation by running:

```bash
composer run-script generate-docs
```

You will find the newly generated documentation at /docs/index.html

This documentation is also linked out and used to display to API users from the dashboard settings page.
