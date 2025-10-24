# Homedocs

Homedocs is a simple personal document manager. You can conveniently store your files by classifying them with tags for easy retrieval in the future.

## Screenshots (click to preview)

<details>
    <summary>Login page</summary>
    <img width="1280" height="607" alt="Signin page screenshot" src="https://github.com/user-attachments/assets/2a8adee1-5993-4893-ae11-a4cfb3c133a1" />
</details>
<details>
    <summary>Default home page / Dashboard</summary>
    <img width="1280" height="1230" alt="Dashboard page screenshot" src="https://github.com/user-attachments/assets/b985c420-6a84-48d6-b927-457276a822d4" />
</details>
<details>
    <summary>Profile page</summary>
    <img width="1280" height="640" alt="Profile page screenshot" src="https://github.com/user-attachments/assets/df521f7d-9ca7-4ffa-8b74-2d2aeb096f84" />
</details>
<details>
    <summary>Document page</summary>
    <img width="1280" height="607" alt="Document page screenshot" src="https://github.com/user-attachments/assets/0a13afd8-db97-4459-abe7-1a05646066db" />
</details>
<details>
    <summary>Search (advanced) page</summary>
    <img width="1280" height="607" alt="Advanced search page screenshot" src="https://github.com/user-attachments/assets/06ab6bbd-aa97-49a2-89ca-0e171a9d6089" />
</details>
<details>
    <summary>Fast search dialog</summary>
    <img width="1280" height="607" alt="Fast search dialog screenshot" src="https://github.com/user-attachments/assets/4234cf3e-fd2f-404d-a842-799dcf41ffd4" />
</details>
<details>
    <summary>Preview attachments page</summary>
    <img width="1280" height="607" alt="PDF preview dialog screenshot" src="https://github.com/user-attachments/assets/ab6b138f-48ec-4faf-aebc-06863ef59883" />
    <img width="1280" height="613" alt="Image preview dialog screenshot" src="https://github.com/user-attachments/assets/421f4681-cba3-432e-86e1-9a01f8fd325f" />
</details>
<details>
    <summary>Dark mode</summary>
    <img width="1280" height="640" alt="Profile page (dark mode)" src="https://github.com/user-attachments/assets/d6d4b259-a17a-4026-91b6-9eacf667b456" />
</details>

## System requirements

- PHP v8.4
- composer
- 'pdo_sqlite' & 'mbstring' php extensions
- enough disk space for storing documents

## Setup / Installation

The project currently consists of two parts:

**phpslim-api/** : Back-end (server API, also includes the latest compiled Quasar web interface/front-end). If you only want to install/use Homedocs, you can copy/use just this part.

**quasar-frontend/**: Front-end (contains the source code of the Quasar web interface). You won’t need this if you only want to use Homedocs (without developing or making changes).

Install the dependencies (in the **phpslim-api/** path).

```Shell
composer install
```

Execute the SQLite database creation/update script.

```Shell
php phpslim-api/tools/install.php
```

Check proper permissions: The web server process must have read and write permissions for files, and read, write, and execute permissions for directories.

```Shell
    Database file
        phpslim-api/data/homedocs3.sqlite3

    Storage paths
        phpslim-api/data/storage/*.*

    Debug files
        phpslim-api/data/logs/*.*
```

The system should work by default, but if you need to modify any configuration, the settings file is located at:

```Shell
    phpslim-api/config/settings.php
```

## Web server configuration:

The base path for the web server should be: **phpslim-api/public/**

Customize the web server settings according to the PHP Slim documentation:

https://www.slimframework.com/docs/v4/start/web-servers.html

## Migration from previous (2.x) version

**WARNING**: Before making any changes, make a backup of the files and database (located at **phpslim-api/data/**).

The storage data is retained/shared from previous versions (1.x & 2.x), but the database structure has changed. You will need to install a clean database and run a small SQLite script from the command line:

```Shell
php phpslim-api/tools/install.php
cd phpslim-api/data
sqlite3 ./homedocs3.sqlite3 < ../tools/migrate-from-v2.sql
```

## Migration from old (1.x) version to previous version (2.x)

**WARNING**: Before making any changes, make a backup of the files and database (located at **phpslim-api/data/**).

The storage data is retained/shared from the previous version (1.0), but the database structure has changed. You will need to run a small SQLite script from the command line (located under the **phpslim-api/data/** path, where old sqlite3 database is stored):

```Shell
cat ../tools/migrate-from-v1.sql | sqlite3 ./homedocs2.sqlite3

```

Once migrated, run the update script to apply the SQL version changes:

```Shell
php phpslim-api/tools/update.php
```

## Backup data

It's highly recommended to make periodic backups. All data (excluding source code), including the SQLite database and file storage, is stored under the **phpslim-api/data/** path.

## Add/recover user credentials from console

You can create or update an account from the command line using the PHP script:

```Shell
php phpslim-api/tools/set-credential.php --email myuser@myserver.net --password mysecret
```

If the account already exists, it will be overwritten with the provided data.

## Development

### Mock / example data

It is possible to generate mock example data for a user with specific credentials using the following command line:

```Shell
php phpslim-api/tools/generate-demo-data.php --count 512 --email johndoe@localhost --password secret
```

### Backend (php-slim)

Install the dependencies:

```Shell
cd phpslim-api
composer install
```

Start the local PHP (backend/API) server, listening on http://127.0.0.1:8081:

```Shell
composer start
```

### Frontend (quasar)

Install the dependencies:

```Shell
cd quasar-frontend
npm i
```

Start the local front-end (Quasar web app interface), listening on http://127.0.0.1:8080:

```Shell
quasar dev
```

Build the Quasar web app release (in **/phpslim-api/public**):

```Shell
quasar buid
```

## TODO

- Docker
- Document API
- Android app

![PHP Composer](https://github.com/aportela/homedocs/actions/workflows/php.yml/badge.svg)
