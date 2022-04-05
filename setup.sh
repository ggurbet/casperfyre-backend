#!/bin/bash

COLOR_RED='\033[0;31m'
COLOR_YELLOW='\033[1;33m'
COLOR_GREEN='\033[1;32m'
COLOR_END='\033[0m'

echo "Setting up Casper Faucet API.."

echo -e "${COLOR_YELLOW}Please enter the domain URL of this server (eg. casperfyre.com)${COLOR_END}"
read -p "Domain URL: " CORS_SITE
if [ -z "$CORS_SITE" ]; then
	echo -e "${COLOR_RED}Please specify a domain URL for your server. ${COLOR_END}"
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

echo -e "${COLOR_YELLOW}Please enter your database password. ${COLOR_END}"
read -p "Database password: " DATABASE_PASSWORD
if [ -z "$DATABASE_PASSWORD" ]; then
	echo -e "${COLOR_RED}Please specify a password for your Mysql database. ${COLOR_END}"
	exit 1
fi

echo -e "${COLOR_YELLOW}Please enter your database user. Default (root) ${COLOR_END}"
read -p "Database password: " DATABASE_USER
if [ -z "$DATABASE_USER" ]; then
	DATABASE_USER="root"
fi

RANDOM_ENTROPY=$(head -c 64 < /dev/urandom)
MASTER_PASSWORD=$(echo $RANDOM_ENTROPY | md5sum)

if [ -f "config.json" ]; then
	cp config.json config.backup.json
fi

cp config.example.json config.json

sed -i "s/\[cors_site\]/$CORS_SITE/g" config.json
sed -i "s/\[node_ip\]/$NODE_IP/g" config.json
sed -i "s/\[secret_key_path\]>/$SECRET_KEY_PATH/g" config.json
sed -i "s/\[database_host\]/$DATABASE_HOST/g" config.json
sed -i "s/\[database_password\]/$DATABASE_PASSWORD/g" config.json
sed -i "s/\[database_user\]/$DATABASE_USER/g" config.json
# sed -i "s/deadbeefdeadbeefdeadbeefdeadbeef/$MASTER_PASSWORD/g" config.json

echo -e "${COLOR_GREEN}[+] Done${COLOR_END}"