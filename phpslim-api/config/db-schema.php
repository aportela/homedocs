<?php

return ([
    3 => [
        '
            CREATE TABLE USER (
                id TEXT NOT NULL CHECK(length(id) == 36),
                email TEXT NOT NULL UNIQUE CHECK(length(email) <= 255),
                password_hash TEXT NOT NULL CHECK(length(password_hash) <= 60),
                ctime INTEGER NOT NULL,
                mtime INTEGER,
                PRIMARY KEY (id)
            ) STRICT;

            CREATE TABLE DOCUMENT (
                id TEXT NOT NULL CHECK(length(id) == 36),
                title TEXT NOT NULL CHECK(length(title) <= 128),
                description TEXT NULL CHECK(length(description) <= 8192),
                PRIMARY KEY (id)
            ) STRICT;

            CREATE INDEX idx_document_title ON DOCUMENT (title);

            CREATE TABLE DOCUMENT_TAG (
                document_id TEXT NOT NULL CHECK(length(document_id) == 36),
                tag TEXT NOT NULL CHECK(length(tag) <= 32),
                PRIMARY KEY (document_id, tag),
                FOREIGN KEY (document_id) REFERENCES DOCUMENT(id)
            ) STRICT;

            CREATE INDEX idx_document_tag_document_id ON DOCUMENT_TAG (document_id);

            CREATE TABLE ATTACHMENT (
                id TEXT NOT NULL CHECK(length(id) == 36),
                sha1_hash TEXT NOT NULL CHECK(length(sha1_hash) == 40),
                name TEXT NOT NULL CHECK(length(name) <= 256),
                size INTEGER NOT NULL,
                cuid TEXT NOT NULL CHECK(length(cuid) == 36),
                ctime INTEGER NOT NULL,
                PRIMARY KEY (id),
                FOREIGN KEY (cuid) REFERENCES USER(id)
            ) STRICT;

            CREATE INDEX idx_attachment_cuid_user_id ON ATTACHMENT (cuid);

            CREATE TABLE DOCUMENT_ATTACHMENT (
                document_id TEXT NOT NULL CHECK(length(document_id) == 36),
                attachment_id TEXT NOT NULL CHECK(length(attachment_id) == 36),
                PRIMARY KEY (document_id, attachment_id),
                FOREIGN KEY (document_id) REFERENCES DOCUMENT(id),
                FOREIGN KEY (attachment_id) REFERENCES ATTACHMENT(id)
            ) STRICT;

            CREATE INDEX idx_document_attachment_document_id ON DOCUMENT_ATTACHMENT (document_id);
            CREATE INDEX idx_document_attachment_attachment_id ON DOCUMENT_ATTACHMENT (attachment_id);

            CREATE TABLE DOCUMENT_NOTE (
                note_id TEXT NOT NULL CHECK(length(note_id) == 36),
                document_id TEXT NOT NULL CHECK(length(document_id) == 36),
                ctime INTEGER NOT NULL,
                cuid TEXT NOT NULL CHECK(length(cuid) == 36),
                body TEXT NULL CHECK(length(body) <= 16384),
                PRIMARY KEY (note_id),
                FOREIGN KEY (document_id) REFERENCES DOCUMENT(id),
                FOREIGN KEY (cuid) REFERENCES USER(id)
            ) STRICT;

            CREATE INDEX idx_document_note_document_id ON DOCUMENT_NOTE (document_id);
            CREATE INDEX idx_document_note_user_id ON DOCUMENT_NOTE (cuid);

            CREATE TABLE DOCUMENT_HISTORY (
                document_id TEXT NOT NULL CHECK(length(document_id) == 36),
                ctime INTEGER NOT NULL,
                operation_type INTEGER NOT NULL,
                cuid TEXT NOT NULL CHECK(length(cuid) == 36),
                PRIMARY KEY (document_id, ctime, operation_type, cuid),
                FOREIGN KEY (document_id) REFERENCES DOCUMENT(id),
                FOREIGN KEY (cuid) REFERENCES USER(id)
            ) STRICT;

            CREATE INDEX idx_document_history_document_id ON DOCUMENT_HISTORY (document_id);
            CREATE INDEX idx_document_history_user_id ON DOCUMENT_HISTORY (cuid);

            CREATE TABLE SHARED_ATTACHMENT (
                id TEXT NOT NULL UNIQUE CHECK(length(id) <= 200),
                cuid TEXT NOT NULL CHECK(length(cuid) == 36),
                attachment_id TEXT NOT NULL UNIQUE CHECK(length(attachment_id) == 36),
                ctime INTEGER NOT NULL,
                etime INTEGER NOT NULL,
                access_limit INTEGER NOT NULL,
                access_count INTEGER NOT NULL,
                enabled INTEGER NOT NULL,
                PRIMARY KEY (attachment_id),
                FOREIGN KEY (cuid) REFERENCES USER(id),
                FOREIGN KEY (attachment_id) REFERENCES ATTACHMENT(id)
            ) STRICT;
        ',
    ]
]);
