# Homedocs

Homedocs is is a simple personal document manager. You can conveniently store your files by classifying them with tags for easy retrieval in the future.

## Screenshots

### Sign in page

<img width="1280" height="607" alt="Signin page screenshot" src="https://github.com/user-attachments/assets/2a8adee1-5993-4893-ae11-a4cfb3c133a1" />

### Default home page / Dashboard

<img width="1280" height="1230" alt="Dashboard page screenshot" src="https://github.com/user-attachments/assets/b985c420-6a84-48d6-b927-457276a822d4" />

### Profile page

<img width="1280" height="640" alt="Profile page screenshot" src="https://github.com/user-attachments/assets/df521f7d-9ca7-4ffa-8b74-2d2aeb096f84" />

### Document page

<img width="1280" height="607" alt="Document page screenshot" src="https://github.com/user-attachments/assets/0a13afd8-db97-4459-abe7-1a05646066db" />

### Search (advanced) page

<img width="1280" height="607" alt="Advanced search page screenshot" src="https://github.com/user-attachments/assets/06ab6bbd-aa97-49a2-89ca-0e171a9d6089" />

### Fast search dialog

<img width="1280" height="607" alt="Fast search dialog screenshot" src="https://github.com/user-attachments/assets/4234cf3e-fd2f-404d-a842-799dcf41ffd4" />

### Preview attachments page

<img width="1280" height="607" alt="PDF preview dialog screenshot" src="https://github.com/user-attachments/assets/ab6b138f-48ec-4faf-aebc-06863ef59883" />

<img width="1280" height="613" alt="Image preview dialog screenshot" src="https://github.com/user-attachments/assets/421f4681-cba3-432e-86e1-9a01f8fd325f" />

### Dark mode

<img width="1280" height="640" alt="Profile page (dark mode)" src="https://github.com/user-attachments/assets/d6d4b259-a17a-4026-91b6-9eacf667b456" />

## System requirements

- PHP v8.4
- composer
- 'pdo_sqlite' & 'mbstring' php extensions
- enough disk space for storing documents

## Setup / Installation

The project currently consists of two parts

**phpslim-api/** : full server api (includes latest compiled quasar web frontend). If you only want to install/use homedocs you can copy/use this path.

**quasar-frontend/**: Contains the source code of the frontend (quasar). You won't need this if you only want to use homedocs (without programming or making changes).

Install dependencies (under phpslim-api/ path):

```Shell
composer install
```

Execute sqlite database creation/update script

```Shell
php phpslim-api/tools/install.php
```

Check proper permissions, web server process must read/write (files) read/write/execute (dirs) on

```Shell
    Database file
        phpslim-api/data/homedocs2.sqlite3

    Storage paths
        phpslim-api/data/storage/*.*

    Debug files
        phpslim-api/data/logs/*.*
```

The system should work by default but if you need to modify any configuration the settings file is located at

```Shell
    phpslim-api/config/settings.php
```

## Web server configuration:

The base path you should use for the web server is **phpslim-api/public/**

Customize web server settings according to php slim docs:

https://www.slimframework.com/docs/v4/start/web-servers.html

## Migration from previous (2.x) version

**WARNING**: Before making any changes, make a backup copy of the files and database (under path **phpslim-api/data/**)

The storage data is kept/shared from the previous versions (1.x && 2.x) but the database version structure has changed and you will need to install (clean) database and run a small sqlite script from the command line:

```Shell
php phpslim-api/tools/install.php
cd phpslim-api/data
sqlite3 ./homedocs3.sqlite3 < ../tools/migrate-from-v2.sql
```

## Migration from old (1.x) version to previous version (2.x)

**WARNING**: Before making any changes, make a backup copy of the files and database (under path **phpslim-api/data/**)

The storage data is kept/shared from the previous version (1.0) but the database version structure has changed and you will need to run a small sqlite script from the command line (under **phpslim-api/data/** path, where old sqlite3 database is stored):

```Shell
cat ../tools/migrate-from-v1.sql | sqlite3 ./homedocs2.sqlite3

```

Once migrated, run the update script that applies sql version changes

```Shell
php phpslim-api/tools/update.php
```

## Backup data

You really want to make (periodic) backups, all (without source code) data (sqlite database & file storage) is stored on **phpslim-api/data/** path

## Add/recover user credentials from console

You can create/update an account from the command line with the php script

```Shell
php phpslim-api/tools/set-credential.php --email myuser@myserver.net --password mysecret
```

If the account already exists it will be overwritten with the data provided.

## Development

### Mock / example data

Is it possible to create a mock of example data for a user with specific credentials using the following command line:

```Shell
php phpslim-api/tools/generate-demo-data.php --count 512 --email johndoe@localhost --password secret
```

### Backend (php-slim)

Install dependencies

```Shell
cd phpslim-api
composer install
```

Start local php server listening on http://127.0.0.1:8081:

```Shell
composer start
```

### Frontend (quasar)

Install dependencies

```Shell
cd quasar-frontend
npm i
```

Start local front-end webapp listening on http://127.0.0.1:8080:

```Shell
quasar dev
```

Build quasar webapp release (on /phpslim-api/public):

```Shell
quasar buid
```

## TODO

- Docker
- Document API
- Android app

![PHP Composer](https://github.com/aportela/homedocs/actions/workflows/php.yml/badge.svg)
