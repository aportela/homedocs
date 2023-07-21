# Warning - Beta release

This software may contain bugs, so use under your own risk

# Homedocs

Homedocs is is a simple personal document manager. You can conveniently store your files by classifying them with tags for easy retrieval in the future.

## Screenshots

## System requirements

- PHP v8.x
- composer
- 'pdo_sqlite' & 'mbstring' php extensions
- enough disk space for storing documents

## Setup / Installation

The project currently consists of two parts

**phpslim-api/** : full server api (includes latest compiled quasar web frontend). If you only want to install/use homedocs you can copy/use this path.

**quasar-frontend/**: Contains the source code of the frontend (quasar). You won't need this if you only want to use homedocs (without programming or making changes).

Install dependencies (under phpslim-api/ path):

```
    composer install
```

Execute sqlite database creation/update script

```
    php phpslim-api/tools/install.php
```

Check proper permissions, web server process must read/write (files) read/write/execute (dirs) on

```
    Database file
        phpslim-api/data/homedocs2.sqlite3

    Storage paths
        phpslim-api/data/storage/*.*

    Debug files
        phpslim-api/logs/*.*
```

The system should work by default but if you need to modify any configuration the settings file is located at

```
    phpslim-api/config/settings.php
```

## Web server configuration:

The base path you should use for the web server is **phpslim-api/public/**

Customize web server settings according to php slim docs:

https://www.slimframework.com/docs/v4/start/web-servers.html

## Migration from old (1.x) version

**WARNING**: Before making any changes, make a backup copy of the files and database (under path **phpslim-api/data/**)

The storage data is kept/shared from the previous version (1.0) but the database version structure has changed and you will need to run a small sqlite script from the command line (under **phpslim-api/data/** path, where old sqlite3 database is stored):

```
cat ..\tools\migrate-from-v1.sql | sqlite3 .\homedocs2.sqlite3

```

Once migrated, run the update script that applies sql version changes

```
    php phpslim-api/tools/install.php
```

## Backup data

You really want to make (periodic) backups, all (without source code) data (sqlite database & file storage) is stored on **phpslim-api/data/** path

## Add/recover user credentials

You can create/update an account from the command line with the php script

```
    php phpslim-api/tools/set-credential.php --email myuser@myserver.net --password mysecret
```

Once migrated, run the script
If the account already exists it will be overwritten with the data provided.

## TODO

- Document quasar frontend
- Docker
- Document API
- Android app
