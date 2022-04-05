# Casper FYRE API backend
Dispensory API interface for Casper mainnet

## Setup

```
cp .env.example .env
```

Adjust your .env configuration vairables to fit your instance.

### Database

You will need to create the database itself manually. e.g. "casperfyre-db"

Then, the first time pinging the server will build the tables inside the given named database and create an admin account.

Find admin credentials in apache log. Example:

````
[CasperFYRE 2022-04-05T12:28:46-04:00] - DB: Created api_keys table
[CasperFYRE 2022-04-05T12:28:46-04:00] - DB: Created ips table
[CasperFYRE 2022-04-05T12:28:46-04:00] - DB: Created limits table
[CasperFYRE 2022-04-05T12:28:46-04:00] - DB: Created orders table
[CasperFYRE 2022-04-05T12:28:46-04:00] - DB: Created schedule table
[CasperFYRE 2022-04-05T12:28:46-04:00] - DB: Created sessions table
[CasperFYRE 2022-04-05T12:28:46-04:00] - DB: Created settings table
[CasperFYRE 2022-04-05T12:28:46-04:00] - DB: Created user table
[CasperFYRE 2022-04-05T12:28:46-04:00] - Created admin
[CasperFYRE 2022-04-05T12:28:46-04:00] - Email: "email"
[CasperFYRE 2022-04-05T12:28:46-04:00] - Password: "password"
[CasperFYRE 2022-04-05T12:28:46-04:00] - DB: Created wallets table
````