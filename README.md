# Warning - Beta release

This software may contain bugs, so use under your own risk

# Homedocs

Homedocs is is a simple personal document manager. You can conveniently store your files by classifying them with tags for easy retrieval in the future.

## Screenshots

<a href="https://github.com/aportela/homedocs/assets/705838/cab38a76-8483-46b4-afb1-20ae2b495316" target="_blank"><img src="https://github.com/aportela/homedocs/assets/705838/cab38a76-8483-46b4-afb1-20ae2b495316" width="18%" alt="Sign In screenshot"></img></a>

<a href="https://github.com/aportela/homedocs/assets/705838/bd6ada10-fac6-44dd-989d-f2f30f5e4405" target="_blank"><img src="https://github.com/aportela/homedocs/assets/705838/bd6ada10-fac6-44dd-989d-f2f30f5e4405" width="18%" alt="Dashboard screenshot"></img></a>

<a href="https://github.com/aportela/homedocs/assets/705838/dc11dd33-6581-4a05-a989-d906a2a686ae" target="_blank"><img src="https://github.com/aportela/homedocs/assets/705838/dc11dd33-6581-4a05-a989-d906a2a686ae" width="18%" alt="Search screenshot"></img></a>

<a href="https://github.com/aportela/homedocs/assets/705838/76439723-1be3-4c2a-8108-af3051953f44" target="_blank"><img src="https://github.com/aportela/homedocs/assets/705838/76439723-1be3-4c2a-8108-af3051953f44" width="18%" alt="Document screenshot"></img></a>

<a href="https://github.com/aportela/homedocs/assets/705838/3acc3cef-165b-425e-97cf-c2935f67036c" target="_blank"><img src="https://github.com/aportela/homedocs/assets/705838/3acc3cef-165b-425e-97cf-c2935f67036c" width="18%" alt="Attachments preview screenshot"></img></a>

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

## Add/recover user credentials from console

You can create/update an account from the command line with the php script

```
    php phpslim-api/tools/set-credential.php --email myuser@myserver.net --password mysecret
```

If the account already exists it will be overwritten with the data provided.

## TODO

- Document quasar frontend
- Docker
- Document API
- Android app
