# TODO: WARNING PENDING INSTALL / UPDATE DOCUMENTATION AFTER MERGE QUASAR BRANCH, PLEASE WAIT...

# HomeDocs

HomeDocs was created with the simple idea of storing (for quick future searching) typical home documents.

## Warning - Beta release

This software may contain bugs, so use under your own risk

## System requirements

- PHP v7.x
- 'pdo_sqlite' & 'mbstring' php extensions
- enough disk space for storing documents

## Setup / Installation

Install dependencies

```
    composer install
```

Execute sqlite database creation/update script

```
    php tools\install-upgrade-db.php
```

Check proper permissions, web server process must read/write (files) read/write/execute (dirs) on

```
    Database file
        data\homedocs.sqlite3

    Storage paths
        data\storage\*.*

    Debug files
        logs\*.*
```

Customize settings

```
    src/AppSettings.php
```

## Web server configurations (according to [php slim docs](https://www.slimframework.com/docs/v3/start/web-servers.html)):

#### nginx

TODO: access through sub-folder

If you want to use a virtual host, the configuration would be the following:

```
server {
    # server listening port
    listen 80;
    # server full qualified domain name
    server_name www.mydomain.com;
    index index.php;
    # complete local path of homedocs repository
    root /var/www/nginx/homedocs/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ \.php {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
        fastcgi_index index.php;
        # uncomment this (with your address/port settings) for using php fpm connection via tcp socket
        #fastcgi_pass 127.0.0.1:9000;
        # uncomment this (with your path) for using php fpm via unix socket
        fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
    }
}
```

#### apache

If you want to access through a sub-folder of the server (example http://www.mydomain.com/homedocs) you do not have to do anything. Just unzip the package in the webserver root path folder, ex: /var/www/homedocs)

If you want to use a virtual host, the configuration would be the following:

```
<VirtualHost *:80>
        ServerName www.mydomain.com

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/homedocs/

        <Directory /var/www/homedocs/>
                Options +Indexes +FollowSymLinks
                AllowOverride All
                Require all granted
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/homedocs-error.log
        CustomLog ${APACHE_LOG_DIR}/homedocs-access.log combined
</VirtualHost>
```

## Migration from old version

Open file tools/migrate-from-mysql-to-sqlite.php and set database/storage settings

- Old mysql server (file include/configuration.php) configuration on $oldDatabaseSettings

- Old storage local path (file include/configuration.php, constant LOCAL_STORAGE_PATH) on $oldStoragePath

Execute migration

```
    php tools/migrate-from-mysql-to-sqlite.php
```

## Backup data

You really want to make (periodic) backups, all (without source code) data (sqlite database & file storage) is stored on **data/** path

## Add/recover user credentials

Add/update user credentials from commandline

```
    php tools\set-credential.php --email jdoe@foobar.com --password themostsecretpassword
```
