ATTACH DATABASE "homedocs2.sqlite3" AS homedocs2;

DELETE FROM USER;

INSERT INTO USER
    (id, email, password_hash, created_on_timestamp, last_update_timestamp)

SELECT
    homedocs2.USER.id, homedocs2.USER.email, password_hash, COALESCE(MIN_OPERATION.created_on_timestamp, 0), COALESCE(MAX_OPERATION.last_update_timestamp, 0)
FROM homedocs2.USER
LEFT JOIN (
    SELECT
        MIN(homedocs2.DOCUMENT.created_on_timestamp) AS created_on_timestamp, homedocs2.DOCUMENT.created_by_user_id
    FROM homedocs2.DOCUMENT
    GROUP BY created_by_user_id
) MIN_OPERATION ON MIN_OPERATION.created_by_user_id = homedocs2.USER.id
LEFT JOIN (
    SELECT
        MAX(homedocs2.DOCUMENT.created_on_timestamp) AS last_update_timestamp, homedocs2.DOCUMENT.created_by_user_id
    FROM homedocs2.DOCUMENT
    GROUP BY created_by_user_id
) MAX_OPERATION ON MAX_OPERATION.created_by_user_id = homedocs2.USER.id;


DELETE FROM DOCUMENT;

INSERT INTO DOCUMENT
    (id, title, description)

SELECT
    homedocs2.DOCUMENT.id, homedocs2.DOCUMENT.title, homedocs2.DOCUMENT.description
FROM homedocs2.DOCUMENT
ORDER BY homedocs2.DOCUMENT.created_on_timestamp;

DELETE FROM DOCUMENT_TAG;

INSERT INTO DOCUMENT_TAG
    (document_id, tag)

SELECT
    homedocs2.DOCUMENT_TAG.document_id, homedocs2.DOCUMENT_TAG.tag
FROM homedocs2.DOCUMENT_TAG;

DELETE FROM FILE;

INSERT INTO FILE
    (id, sha1_hash, name, size, created_by_user_id, created_on_timestamp)

SELECT
    homedocs2.FILE.id, homedocs2.FILE.sha1_hash, homedocs2.FILE.name, homedocs2.FILE.size, homedocs2.FILE.uploaded_by_user_id, homedocs2.FILE.uploaded_on_timestamp * 1000
FROM homedocs2.FILE
ORDER BY homedocs2.FILE.uploaded_on_timestamp;

DELETE FROM DOCUMENT_FILE;

INSERT INTO DOCUMENT_FILE
    (document_id, file_id)

SELECT
    homedocs2.DOCUMENT_FILE.document_id, homedocs2.DOCUMENT_FILE.file_id
FROM homedocs2.DOCUMENT_FILE;

DELETE FROM DOCUMENT_HISTORY;

INSERT INTO DOCUMENT_HISTORY
    (document_id, created_on_timestamp, operation_type, created_by_user_id)

SELECT
    homedocs2.DOCUMENT.id, homedocs2.DOCUMENT.created_on_timestamp * 1000, 1, homedocs2.DOCUMENT.created_by_user_id
FROM homedocs2.DOCUMENT
