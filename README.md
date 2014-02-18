# HomeDocs

HomeDocs was created with the simple idea of storing (for quick future searching) typical home documents.

## Warning - Beta release

This software may contain bugs, so use under your own risk

## System requirements

- PHP v5.5 (with imagick module)
- mysql
- enough disk space for storing documents

## Setup & Installation

Open include/configuration.php file with editor and set database values & storage path according with your configuration

- DATABASE_HOST
- DATABASE_PORT
- DATABASE_NAME
- DATABASE_USERNAME
- DATABASE_PASSWORD
- ALLOW_NEW_ACCOUNTS: set TRUE to create first account
- LOCAL_STORAGE_PATH: full storage directory path (full permission access required for web server process owner).

Point your browser to install.php file (ex: http://localhost/homedocs/install.php)

**Warning: after install, install.php file must be deleted**

## Using HomeDocs

After login you will be automatically redirected to default workspace page. For uploading new document/s drag & drop files to the designated area or using the Select files button.

Once you have selected and uploaded all the files related to a new document, a popup window appears to fill some document metadata.

## Icon credits

PNG icons by FileSquare - https://www.iconfinder.com/search/?q=iconset:filetypeicons-set-
