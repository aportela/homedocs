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
    ),
    3 => array(
        '
            CREATE TABLE `DOCUMENT_NOTE` (
                `note_id` VARCHAR(36) NOT NULL,
                `document_id` VARCHAR(36) NOT NULL,
                `created_on_timestamp` INTEGER NOT NULL,
                `created_by_user_id` VARCHAR(36) NOT NULL,
                `body` VARCHAR(16384) NULL,
                PRIMARY KEY (`note_id`)
            )
        ',
        '
            CREATE TABLE `DOCUMENT2` (
                `id` VARCHAR(36) NOT NULL,
                `title` VARCHAR(128) NOT NULL,
                `description` VARCHAR(4096) NULL,
                PRIMARY KEY (`id`)
            );
        ',
        '
            INSERT INTO
                DOCUMENT2
            (id, title, description)

            SELECT
                id, title, description
            FROM DOCUMENT
            ORDER BY DOCUMENT.created_by_user_id
        ',
        '
            CREATE TABLE `DOCUMENT_HISTORY` (
                `document_id` VARCHAR(36) NOT NULL,
                `operation_date` INTEGER NOT NULL,
                `operation_type` INTEGER NOT NULL,
                `operation_user_id` VARCHAR(36) NOT NULL,
                PRIMARY KEY (`document_id`, `operation_date`, `operation_type`, `operation_user_id`)
            )
        ',
        '
            INSERT INTO
                DOCUMENT_HISTORY
            (document_id, operation_date, operation_type, operation_user_id)

            SELECT
                DOCUMENT.id, DOCUMENT.created_on_timestamp * 1000, 1, DOCUMENT.created_by_user_id
            FROM DOCUMENT
            ORDER BY DOCUMENT.created_on_timestamp
        ',
        '
            DROP TABLE DOCUMENT;
        ',
        '
            ALTER TABLE DOCUMENT2 RENAME TO DOCUMENT;
        ',
        '
            UPDATE FILE
                SET uploaded_on_timestamp = uploaded_on_timestamp * 1000
        ',
        '
            CREATE TABLE `USER2` (
                `id` VARCHAR(36) NOT NULL,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `password_hash` VARCHAR(60) NOT NULL,
                `created_on_timestamp` INTEGER NOT NULL,
                `last_update_timestamp` INTEGER,
                PRIMARY KEY (`id`)
            );
        ',
        '
            INSERT INTO USER2
                (id, email, password_hash, created_on_timestamp, last_update_timestamp)

            SELECT
                USER.id, USER.email, USER.password_hash, COALESCE(TMP_USER_MIN_DATE.operation_timestamp, 0), TMP_USER_MAX_DATE.operation_timestamp
            FROM USER
            LEFT JOIN (
                SELECT
                    DOCUMENT_HISTORY.operation_user_id, MIN(DOCUMENT_HISTORY.operation_date) AS operation_timestamp
                FROM DOCUMENT_HISTORY
                GROUP BY DOCUMENT_HISTORY.operation_user_id
            ) TMP_USER_MIN_DATE ON TMP_USER_MIN_DATE.operation_user_id = USER.id
            LEFT JOIN (
                SELECT
                    DOCUMENT_HISTORY.operation_user_id, MAX(DOCUMENT_HISTORY.operation_date) AS operation_timestamp
                FROM DOCUMENT_HISTORY
                GROUP BY DOCUMENT_HISTORY.operation_user_id
            ) TMP_USER_MAX_DATE ON TMP_USER_MAX_DATE.operation_user_id = USER.id;
        ',
        '
            DROP TABLE `USER`;
        ',
        '
            ALTER TABLE USER2 RENAME TO USER;
        ',

    )
));
