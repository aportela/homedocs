DROP TABLE VERSION;
CREATE TABLE "VERSION" (
"release_number" INTEGER NOT NULL,
"release_date" STRING NOT NULL,
PRIMARY KEY("release_number")
);
INSERT INTO "VERSION" (release_number, release_date) VALUES (2, datetime());
.save homedocs2.sqlite3