#!/bin/bash

COLOR_RED='\033[0;31m'
COLOR_YELLOW='\033[1;33m'
COLOR_GREEN='\033[1;32m'
COLOR_END='\033[0m'

echo -e "Setting up CasperFYRE API.."
echo
echo -e "Please have your database and vhost details ready before starting this setup."
read -p "Press enter to continue" READY
echo
echo

echo -e "${COLOR_YELLOW}Please enter the main URL of this server (eg. casperfyre.com)${COLOR_END}"
read -p "Main domain URL: " FRONTEND_URL
if [ -z "$FRONTEND_URL" ]; then
	echo -e "${COLOR_RED}Please specify a main URL for your server. ${COLOR_END}"
	exit 1
fi

echo -e "${COLOR_YELLOW}Please enter the API URL of this server (eg. api.casperfyre.com)${COLOR_END}"
read -p "API domain URL: " CORS_SITE
if [ -z "$CORS_SITE" ]; then
	echo -e "${COLOR_RED}Please specify a API URL for your server. ${COLOR_END}"
	exit 1
fi

echo -e "${COLOR_YELLOW}Please enter a known validator IP. Default (18.219.70.138)${COLOR_END}"
read -p "Node IP: " NODE_IP
if [ -z "$NODE_IP" ]; then
	NODE_IP="18.219.70.138"
fi

echo -e "${COLOR_YELLOW}Please enter your database hostname. Default (localhost)${COLOR_END}"
read -p "Database host: " DATABASE_HOST
if [ -z "$DATABASE_HOST" ]; then
	DATABASE_HOST="localhost"
fi

echo -e "${COLOR_YELLOW}Please enter your database user. Default (root) ${COLOR_END}"
read -p "Database password: " DATABASE_USER
if [ -z "$DATABASE_USER" ]; then
	DATABASE_USER="root"
fi

echo -e "${COLOR_YELLOW}Please enter your database password. ${COLOR_END}"
read -s -p "Database password: " DATABASE_PASSWORD
if [ -z "$DATABASE_PASSWORD" ]; then
	echo -e "${COLOR_RED}Please specify a password for your Mysql database. ${COLOR_END}"
	exit 1
fi

echo -e "${COLOR_YELLOW}Please enter your VHOST document directory for the backend API (eg. /var/www/casperfyre-backend/public). ${COLOR_END}"
read -s -p "VHOST directory: " API_VHOST
if [ -z "$API_VHOST" ]; then
	echo -e "${COLOR_RED}Please specify your API VHOST directory. ${COLOR_END}"
	exit 1
fi

echo -e "${COLOR_YELLOW}Generating entropy...${COLOR_END}"

RANDOM_ENTROPY=$(head -c 64 < /dev/urandom)
MASTER_KEY=$(echo $RANDOM_ENTROPY | md5sum | cut -d' ' -f1)

echo -e "${COLOR_GREEN}Key created${COLOR_END}"

if [ -f ".env" ]; then
	if [ -f ".env.backup" ]; then
		echo -e "${COLOR_YELLOW}.env.backup already exists${COLOR_END}"
	else
		cp .env .env.backup
	fi
fi

cp .env.example .env

echo -e "${COLOR_YELLOW}Configuring...${COLOR_END}"

sed -i "s/\[FRONTEND_URL\]/$FRONTEND_URL/g" .env
sed -i "s/\[CORS_SITE\]/$CORS_SITE/g" .env
sed -i "s/\[NODE_IP\]/$NODE_IP/g" .env
sed -i "s/\[DB_HOST\]/$DATABASE_HOST/g" .env
sed -i "s/\[DB_USER\]/$DATABASE_USER/g" .env
sed -i "s/\[DB_PASS\]/$DATABASE_PASSWORD/g" .env
sed -i "s/\[MASTER_KEY\]/$MASTER_KEY/g" .env

echo -e "${COLOR_YELLOW}Checking for required software...${COLOR_END}"

OS_VERSION=$(cat /etc/os-release | awk -F '=' '{print $2}' | tr -d '"' | sed -n '1 p')
OS_RELEASE=$(cat /etc/os-release | awk -F '=' '{print $2}' | tr -d '"' | sed -n '2 p' | cut -d'.' -f1)

if ! command -v apache2 &> /dev/null; then
	APACHE_VERSION=0
else
	APACHE_VERSION=$(apache2 -v | tr -d '\n' | cut -d'/' -f2 | cut -d'.' -f1)
fi

if ! command -v php &> /dev/null; then
	PHP_VERSION=0
else
	PHP_VERSION=$(php --version | cut -d' ' -f2 | tr -d '\n' | cut -d"." -f1)
fi

if ! command -v mysql &> /dev/null; then
	MYSQL_VERSION=0
else
	MYSQL_VERSION=$(mysql --version | cut -d'.' -f1 | awk '{print $3}')
fi

if ! command -v composer &> /dev/null; then
	COMPOSER_VERSION=0
else
	COMPOSER_VERSION=$(composer --version | cut -d' ' -f3 | cut -d'.' -f1)
fi

if ! command -v node &> /dev/null; then
	NODEJS_VERSION=0
else
	NODEJS_VERSION=$(node --version | cut -d'.' -f1 | tr -d 'v')
fi

if ! command -v npm &> /dev/null; then
	NPM_VERSION=0
else
	NPM_VERSION=$(npm --version | cut -d'.' -f1)
fi

if ! command -v yarn &> /dev/null; then
	YARN_VERSION=0
else
	YARN_VERSION=$(yarn --version | cut -d'.' -f1)
fi


if [ ! $OS_VERSION = 'Ubuntu' ]; then
	echo -e "${COLOR_RED}This software is only tested to work on Ubuntu systems${COLOR_END}"
	exit 1
fi

if [ $OS_RELEASE -lt 18 ]; then
	echo -e "${COLOR_RED}You are using Ubuntu version $OS_RELEASE. Please use at least Ubuntu version 18${COLOR_END}"
	exit 1
fi

if [ $APACHE_VERSION -lt 2 ]; then
	echo -e "${COLOR_RED}You are using Apache version $APACHE_VERSION. Please install at least Apache version 2.4${COLOR_END}"
	exit 1
fi

if [ $PHP_VERSION -lt 7 ]; then
	echo -e "${COLOR_RED}You are using PHP version $PHP_VERSION. Please install at least PHP version 7${COLOR_END}"
	exit 1
fi

if [ $MYSQL_VERSION -lt 5 ]; then
	echo -e "${COLOR_RED}You are using Mysql version $MYSQL_VERSION. Please innstall at least Mysql version 5${COLOR_END}"
	exit 1
fi

sudo apt -y install curl
sudo a2enmod rewrite
sudo a2enmod headers
sudo service apache2 restart

if [ $COMPOSER_VERSION -lt 2 ]; then
	echo -e "${COLOR_YELLOW}Installing composer${COLOR_END}"
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
	php -r "unlink('composer-setup.php');"
fi

composer install
composer update

echo -e "${COLOR_YELLOW}Installing cronjobs${COLOR_END}"
(crontab -l 2>>/dev/null; echo "* * * * * curl -s $CORS_SITE/cron/schedule >> dev/null 2>&1") | crontab -
(crontab -l 2>>/dev/null; echo "* * * * * curl -s $CORS_SITE/cron/orders >> dev/null 2>&1") | crontab -
(crontab -l 2>>/dev/null; echo "*/5 * * * * curl -s $CORS_SITE/cron/verify-orders >> dev/null 2>&1") | crontab -
(crontab -l 2>>/dev/null; echo "2 * * * * curl -s $CORS_SITE/cron/garbage >> dev/null 2>&1") | crontab -

echo -e "${COLOR_GREEN}[+] Done${COLOR_END}"
echo 
echo -e "Please note, you will still need to setup emailer credentials information in your .env before the email scheduler will work."
