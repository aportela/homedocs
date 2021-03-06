SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DROP TABLE IF EXISTS `DOCUMENT`;
CREATE TABLE IF NOT EXISTS `DOCUMENT` (
  `ID` binary(16) NOT NULL,
  `USER_ID` binary(16) NOT NULL,
  `CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TITLE` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `DESCRIPTION` varchar(4096) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `DESCRIPTION` (`DESCRIPTION`(255)),
  KEY `TITLE` (`TITLE`),
  KEY `USER_ID` (`USER_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `DOCUMENT_FILE`;
CREATE TABLE IF NOT EXISTS `DOCUMENT_FILE` (
  `DOCUMENT_ID` binary(16) NOT NULL,
  `FILE_ID` binary(16) NOT NULL,
  PRIMARY KEY (`DOCUMENT_ID`,`FILE_ID`),
  KEY `DOCUMENT_ID` (`DOCUMENT_ID`),
  KEY `FILE_ID` (`FILE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `DOCUMENT_TAG`;
CREATE TABLE IF NOT EXISTS `DOCUMENT_TAG` (
  `DOCUMENT_ID` binary(16) NOT NULL,
  `TAG` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`DOCUMENT_ID`,`TAG`),
  KEY `TAG` (`TAG`),
  KEY `FILE_ID` (`DOCUMENT_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `FILE`;
CREATE TABLE IF NOT EXISTS `FILE` (
  `ID` binary(16) NOT NULL,
  `USER_ID` binary(16) NOT NULL,
  `SHA1_HASH` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `NAME` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `UPLOADED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SIZE` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `NAME` (`NAME`(255),`UPLOADED`),
  KEY `SIZE` (`SIZE`),
  KEY `USER_ID` (`USER_ID`),
  KEY `SHA1_HASH` (`SHA1_HASH`),
  KEY `UPLOADED` (`UPLOADED`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `USER`;
CREATE TABLE IF NOT EXISTS `USER` (
  `ID` binary(16) NOT NULL,
  `EMAIL` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `PASSWORD` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `EMAIL` (`EMAIL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
