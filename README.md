# Homedocs

Homedocs is is a simple personal document manager. You can conveniently store your files by classifying them with tags for easy retrieval in the future.

## Screenshots

|                                                                                                                         Sign In                                                                                                                          |                                                                                                                         Dashboard                                                                                                                          |                                                                                                                         Search                                                                                                                          |                                                                                                                         Document                                                                                                                          |                                                                                                                         Attachments preview                                                                                                                          |
| :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------: | :--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------: | :-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------: | :-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------: | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------: |
| <a href="https://github.com/aportela/homedocs/assets/705838/cab38a76-8483-46b4-afb1-20ae2b495316" target="_blank"><img src="https://github.com/aportela/homedocs/assets/705838/cab38a76-8483-46b4-afb1-20ae2b495316" alt="Sign In screenshot"></img></a> | <a href="https://github.com/aportela/homedocs/assets/705838/bd6ada10-fac6-44dd-989d-f2f30f5e4405" target="_blank"><img src="https://github.com/aportela/homedocs/assets/705838/bd6ada10-fac6-44dd-989d-f2f30f5e4405" alt="Dashboard screenshot"></img></a> | <a href="https://github.com/aportela/homedocs/assets/705838/dc11dd33-6581-4a05-a989-d906a2a686ae" target="_blank"><img src="https://github.com/aportela/homedocs/assets/705838/dc11dd33-6581-4a05-a989-d906a2a686ae" alt="Search screenshot"></img></a> | <a href="https://github.com/aportela/homedocs/assets/705838/76439723-1be3-4c2a-8108-af3051953f44" target="_blank"><img src="https://github.com/aportela/homedocs/assets/705838/76439723-1be3-4c2a-8108-af3051953f44" alt="Document screenshot"></img></a> | <a href="https://github.com/aportela/homedocs/assets/705838/3acc3cef-165b-425e-97cf-c2935f67036c" target="_blank"><img src="https://github.com/aportela/homedocs/assets/705838/3acc3cef-165b-425e-97cf-c2935f67036c" alt="Attachments preview screenshot"></img></a> |

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
        phpslim-api/logs/*.*
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

For building release, qpdfviewer quasar-extension is required, use this for install:

```Shell
quasar ext add @quasar/qpdfviewer
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

```
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
