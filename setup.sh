#!/bin/bash

COLOR_RED='\033[0;31m'
COLOR_YELLOW='\033[1;33m'
COLOR_GREEN='\033[1;32m'
COLOR_END='\033[0m'

echo -e "${COLOR_GREEN}Setting up CasperFYRE API..${COLOR_END}"
echo

while true; do
	read -p "Is this for a dev build? (y/n) >" YN
	case $YN in
		[yY] ) DEV_BUILD=true;
			break;;
		[nN] ) echo;
			break;;
		* ) ;;
	esac
done

if [ -z "$DEV_BUILD" ]; then
	echo "PRODUCTION BUILD"
	echo
	echo -e "Please have your database details ready before starting this setup."
	read -p "Press enter to continue >" READY
	echo
	echo

	echo -e "${COLOR_YELLOW}Please enter the main URL of this server. Default (casperfyre.com)${COLOR_END}"
	read -p "Main domain URL: " FRONTEND_URL
	if [ -z "$FRONTEND_URL" ]; then
		FRONTEND_URL="casperfyre.com"
	fi

	echo -e "${COLOR_YELLOW}Please enter the API URL of this server. Default (api.casperfyre.com)${COLOR_END}"
	read -p "API domain URL: " CORS_SITE
	if [ -z "$CORS_SITE" ]; then
		CORS_SITE="api.casperfyre.com"
	fi

	DATABASE_TYPE="mysql"

	echo -e "${COLOR_YELLOW}Please enter your database hostname. Default (localhost)${COLOR_END}"
	read -p "Database host: " DATABASE_HOST
	if [ -z "$DATABASE_HOST" ]; then
		DATABASE_HOST="localhost"
	fi

	echo -e "${COLOR_YELLOW}Please enter your database user. Default (root) ${COLOR_END}"
	read -p "Database username: " DATABASE_USER
	if [ -z "$DATABASE_USER" ]; then
		DATABASE_USER="root"
	fi

	echo -e "${COLOR_YELLOW}Please enter your database password. ${COLOR_END}"
	read -s -p "Database password: " DATABASE_PASSWORD
	if [ -z "$DATABASE_PASSWORD" ]; then
		echo -e "${COLOR_RED}Please specify a password for your Mysql database. ${COLOR_END}"
		exit 1
	fi

else
	echo "DEV BUILD"
	FRONTEND_URL="GITPOD:3000"
	CORS_SITE="GITPOD:3001"
	DATABASE_TYPE="sqlite"
	DATABASE_HOST=""
	DATABASE_USER=""
	DATABASE_PASSWORD=""

	rm database.sqlite
fi

echo -e "${COLOR_YELLOW}Your email address (Admin email)${COLOR_END}"
read -p "Your email: " ADMIN_EMAIL
if [ -z "$ADMIN_EMAIL" ]; then
	echo -e "${COLOR_RED}Please specify an email address to use for the initial admin. ${COLOR_END}"
	exit 1
fi

NODE_IP="18.219.70.138"

echo -e "${COLOR_GREEN}Generating entropy...${COLOR_END}"

RANDOM_ENTROPY=$(head -c 64 < /dev/urandom)
MASTER_KEY=$(echo $RANDOM_ENTROPY | md5sum | cut -d' ' -f1)
RANDOM_ENTROPY2=$(head -c 64 < /dev/urandom)
CRON_TOKEN=$(echo $RANDOM_ENTROPY2 | md5sum | cut -d' ' -f1)

echo -e "${COLOR_GREEN}Keys created${COLOR_END}"

if [ -f ".env" ]; then
	if [ -f ".env.backup" ]; then
		echo -e "${COLOR_YELLOW}.env.backup already exists${COLOR_END}"
	else
		cp .env .env.backup
	fi
fi

cp .env.example .env

echo -e "${COLOR_YELLOW}Configuring...${COLOR_END}"

sed -i "s/\[ADMIN_EMAIL\]/$ADMIN_EMAIL/g" .env
sed -i "s/\[FRONTEND_URL\]/$FRONTEND_URL/g" .env
sed -i "s/\[CORS_SITE\]/$CORS_SITE/g" .env
sed -i "s/\[NODE_IP\]/$NODE_IP/g" .env
sed -i "s/\[DB_TYPE\]/$DATABASE_TYPE/g" .env
sed -i "s/\[DB_HOST\]/$DATABASE_HOST/g" .env
sed -i "s/\[DB_USER\]/$DATABASE_USER/g" .env
sed -i "s/\[DB_PASS\]/$DATABASE_PASSWORD/g" .env
sed -i "s/\[MASTER_KEY\]/$MASTER_KEY/g" .env
sed -i "s/\[CRON_TOKEN\]/$CRON_TOKEN/g" .env

echo -e "${COLOR_YELLOW}Checking for required software...${COLOR_END}"

sudo apt-get update

OS_VERSION=$(cat /etc/os-release | awk -F '=' '{print $2}' | tr -d '"' | sed -n '1 p')
OS_RELEASE=$(cat /etc/os-release | awk -F '=' '{print $2}' | tr -d '"' | sed -n '2 p' | cut -d'.' -f1)

if ! command -v php &> /dev/null; then
	PHP_VERSION=0
else
	PHP_VERSION=$(php --version | cut -d' ' -f2 | tr -d '\n' | cut -d"." -f1)
fi

if ! command -v composer &> /dev/null; then
	COMPOSER_VERSION=0
else
	COMPOSER_VERSION=$(composer --version | cut -d' ' -f3 | cut -d'.' -f1)
fi

if ! command -v casper-client &> /dev/null; then
	CASPERCLIENT_VERSION=0
else
	CASPERCLIENT_VERSION=$(casper-client --version | cut -d' ' -f3 | cut -d'.' -f1)
fi


if [ ! $OS_VERSION = 'Ubuntu' ]; then
	echo -e "${COLOR_RED}This software is only tested to work on Ubuntu systems${COLOR_END}"
	exit 1
fi

if [ $OS_RELEASE -lt 18 ]; then
	echo -e "${COLOR_RED}You are using Ubuntu version $OS_RELEASE. Please use at least Ubuntu version 18${COLOR_END}"
	exit 1
fi

if [ $PHP_VERSION -lt 7 ]; then
	echo -e "${COLOR_YELLOW}Installing PHP 8.1${COLOR_END}"
	sudo add-apt-repository ppa:ondrej/php
	sudo apt-get update
	sudo apt-get install -y php8.1
	sudo apt-get install -y php8.1-{bcmath,bz2,intl,gd,mbstring,mysql,zip,common,curl,xml,gmp,sqlite3}
else
	echo -e "${COLOR_GREEN}PHP installed${COLOR_END}"
fi

if [ $CASPERCLIENT_VERSION -lt 1 ]; then
	echo -e "${COLOR_YELLOW}Installing Casper Client${COLOR_END}"
	echo "deb https://repo.casperlabs.io/releases" bionic main | sudo tee -a /etc/apt/sources.list.d/casper.list
	curl -O https://repo.casperlabs.io/casper-repo-pubkey.asc
	sudo apt-key add casper-repo-pubkey.asc
	sudo apt update
	sudo apt install casper-client
else
	echo -e "${COLOR_GREEN}Casper Client installed${COLOR_END}"
fi

sudo apt -y install curl
sudo chown -R www-data:www-data tmp/

if [ $COMPOSER_VERSION -lt 2 ]; then
	echo -e "${COLOR_YELLOW}Installing composer${COLOR_END}"
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
	php -r "unlink('composer-setup.php');"
fi

composer install
composer update
composer run-script test

echo -e "${COLOR_GREEN}[+] Done${COLOR_END}"
echo 
echo -e "Please note, you will need to setup emailer credentials information in your .env before the email scheduler will work."
echo
echo -e "${COLOR_YELLOW}BACKUP YOUR MASTER KEY --> ${COLOR_GREEN}$MASTER_KEY${COLOR_END}"
echo -e "Wallet keys/token balances will be lost if this key is lost."
