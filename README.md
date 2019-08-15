# HomeDocs

HomeDocs was created with the simple idea of storing (for quick future searching) typical home documents.

## Warning - Beta release

This software may contain bugs, so use under your own risk

## System requirements

- PHP v7.x
- 'pdo_sqlite' & 'mbstring' php extensions
- enough disk space for storing documents

## Setup / Installation

Enter on path "tools" and execute console script install-upgrade-db.php

Check proper permissions, web server process must read/write (files) read/write/execute (dirs) on

    Database file
        data\homedocs.sqlite3

    Storage paths
        data\storage\*.*

    Debug files
        logs\*.*

## Migration from first/old version

TODO