<?php

return (array(
    2 => array(
        " CREATE TABLE IF NOT EXISTS FILES (ID CHAR(40) PRIMARY KEY, DIRECTORY_ID CHAR(40) NOT NULL, NAME VARCHAR(255) NOT NULL, ATIME INTEGER NOT NULL, MTIME INTEGER NOT NULL); ",
        '
            CREATE TABLE `USER` (
                `id` VARCHAR(36) NOT NULL,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `password_hash` VARCHAR(60) NOT NULL,
                PRIMARY KEY (`id`)
            );
        ',
        '
            CREATE TABLE `FILE` (
                `id` VARCHAR(36) NOT NULL,
                `sha1_hash` VARCHAR(40) NOT NULL,
                `name` VARCHAR(256) NOT NULL,
                `size` INTEGER NOT NULL,
                `uploaded_by` VARCHAR(36) NOT NULL,
                `uploaded_on` INTEGER NOT NULL,
                PRIMARY KEY (`id`)
            );
        ',
        '
            CREATE TABLE `DOCUMENT` (
                `id` VARCHAR(36) NOT NULL,
                `title` VARCHAR(128) NOT NULL,
                `description` VARCHAR(4096) NULL,
                `created_by` VARCHAR(36) NOT NULL,
                `uploaded_on` INTEGER NOT NULL,
                PRIMARY KEY (`id`)
            );
        ',
        '
            CREATE TABLE `DOCUMENT_FILE` (
                `document_id` VARCHAR(36) NOT NULL,
                `file_id` VARCHAR(36) NOT NULL,
                PRIMARY KEY (`document_id`, `file_id`)
            );
        ',
        '
            CREATE TABLE `DOCUMENT_TAG` (
                `document_id` VARCHAR(36) NOT NULL,
                `tag` VARCHAR(32) NOT NULL,
                PRIMARY KEY (`document_id`, `tag`)
            )
        ',
        '
            CREATE INDEX idx_user_email ON USER (email);
        ',
        '
            DROP TABLE IF EXISTS `DOCUMENT`;
        ',
        '
            CREATE TABLE `DOCUMENT` (
                `id` VARCHAR(36) NOT NULL,
                `title` VARCHAR(128) NOT NULL,
                `description` VARCHAR(4096) NULL,
                `created_by_user_id` VARCHAR(36) NOT NULL,
                `created_on_timestamp` INTEGER NOT NULL,
                PRIMARY KEY (`id`),
                FOREIGN KEY("created_by_user_id") REFERENCES "USER"("id")
            );
        ',
        '
            CREATE INDEX idx_document_title ON DOCUMENT (title);
        ',
        '
            CREATE INDEX idx_document_description ON DOCUMENT (description);
        ',
        '
            CREATE INDEX idx_document_creator ON DOCUMENT (created_by_user_id);
        ',
        '
            CREATE INDEX idx_document_created_on ON DOCUMENT (created_on_timestamp);
        ',
        '
            DROP TABLE IF EXISTS `FILE`;
        ',
        '
            CREATE TABLE `FILE` (
                `id` VARCHAR(36) NOT NULL,
                `sha1_hash` VARCHAR(40) NOT NULL,
                `name` VARCHAR(256) NOT NULL,
                `size` INTEGER NOT NULL,
                `uploaded_by_user_id` VARCHAR(36) NOT NULL,
                `uploaded_on_timestamp` INTEGER NOT NULL,
                PRIMARY KEY (`id`)
            );
        ',
        '
            CREATE INDEX idx_file_hash ON FILE (sha1_hash);
        ',
        '
            CREATE INDEX idx_file_name ON FILE (name);
        ',
        '
            CREATE INDEX idx_file_size ON FILE (size);
        ',
        '
            CREATE INDEX idx_file_uploader ON FILE (uploaded_by_user_id);
        ',
        '
            CREATE INDEX idx_file_upload_on ON FILE (uploaded_on_timestamp);
        ',
        '
            CREATE INDEX idx_tag_name ON DOCUMENT_TAG (tag);
        '
    )
));
