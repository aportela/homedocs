<?php

return (array(
    3 => array(
        '
            CREATE TABLE USER (
                id CHAR(36) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(60) NOT NULL,
                created_on_timestamp INTEGER NOT NULL,
                last_update_timestamp INTEGER,
                PRIMARY KEY (id)
            );

            CREATE TABLE DOCUMENT (
                id CHAR(36) NOT NULL,
                title VARCHAR(128) NOT NULL,
                description VARCHAR(8192) NULL,
                PRIMARY KEY (id)
            );

            CREATE INDEX idx_document_title ON DOCUMENT (title);

            CREATE TABLE DOCUMENT_TAG (
                document_id CHAR(36) NOT NULL,
                tag VARCHAR(32) NOT NULL,
                PRIMARY KEY (document_id, tag),
                FOREIGN KEY (document_id) REFERENCES DOCUMENT(id)
            );

            CREATE INDEX idx_document_tag_document_id ON DOCUMENT_TAG (document_id);

            CREATE TABLE ATTACHMENT (
                id CHAR(36) NOT NULL,
                sha1_hash CHAR(40) NOT NULL,
                name VARCHAR(256) NOT NULL,
                size INTEGER NOT NULL,
                created_by_user_id CHAR(36) NOT NULL,
                created_on_timestamp INTEGER NOT NULL,
                PRIMARY KEY (id),
                FOREIGN KEY (created_by_user_id) REFERENCES USER(id)
            );

            CREATE INDEX idx_attachment_created_by_user_id_user_id ON ATTACHMENT (created_by_user_id);

            CREATE TABLE DOCUMENT_ATTACHMENT (
                document_id CHAR(36) NOT NULL,
                attachment_id CHAR(36) NOT NULL,
                PRIMARY KEY (document_id, attachment_id),
                FOREIGN KEY (document_id) REFERENCES DOCUMENT(id),
                FOREIGN KEY (attachment_id) REFERENCES ATTACHMENT(id)
            );

            CREATE INDEX idx_document_attachment_document_id ON DOCUMENT_ATTACHMENT (document_id);
            CREATE INDEX idx_document_attachment_attachment_id ON DOCUMENT_ATTACHMENT (attachment_id);

            CREATE TABLE DOCUMENT_NOTE (
                note_id CHAR(36) NOT NULL,
                document_id CHAR(36) NOT NULL,
                created_on_timestamp INTEGER NOT NULL,
                created_by_user_id CHAR(36) NOT NULL,
                body VARCHAR(16384) NULL,
                PRIMARY KEY (note_id),
                FOREIGN KEY (document_id) REFERENCES DOCUMENT(id),
                FOREIGN KEY (created_by_user_id) REFERENCES USER(id)
            );

            CREATE INDEX idx_document_note_document_id ON DOCUMENT_NOTE (document_id);
            CREATE INDEX idx_document_note_user_id ON DOCUMENT_NOTE (created_by_user_id);

            CREATE TABLE DOCUMENT_HISTORY (
                document_id CHAR(36) NOT NULL,
                created_on_timestamp INTEGER NOT NULL,
                operation_type INTEGER NOT NULL,
                created_by_user_id CHAR(36) NOT NULL,
                PRIMARY KEY (document_id, created_on_timestamp, operation_type, created_by_user_id),
                FOREIGN KEY (document_id) REFERENCES DOCUMENT(id),
                FOREIGN KEY (created_by_user_id) REFERENCES USER(id)
            );

            CREATE INDEX idx_document_history_document_id ON DOCUMENT_HISTORY (document_id);
            CREATE INDEX idx_document_history_user_id ON DOCUMENT_HISTORY (created_by_user_id);
        '
    )
));
