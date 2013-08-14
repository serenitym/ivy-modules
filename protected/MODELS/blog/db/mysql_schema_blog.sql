-- phpMyAdmin SQL Dump
-- version 4.0.4.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 31, 2013 at 06:35 PM
-- Server version: 5.5.31-1-log
-- PHP Version: 5.4.4-15.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `blacksea_dev`
--
CREATE DATABASE IF NOT EXISTS `blacksea_dev` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `blacksea_dev`;

-- --------------------------------------------------------

--
-- Table structure for table `blogarticles_records`
--

CREATE TABLE IF NOT EXISTS `blogarticles_records` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `idRecord` int(5) NOT NULL,
  `country` varchar(70) DEFAULT NULL,
  `city` varchar(70) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idRecord` (`idRecord`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `blogComments`
--

CREATE TABLE IF NOT EXISTS `blogComments` (
  `idComm` int(5) NOT NULL AUTO_INCREMENT,
  `idP` int(5) DEFAULT NULL,
  `idExt` int(5) NOT NULL,
  `entryDate` date NOT NULL,
  `userName` char(30) NOT NULL,
  `uidComm` int(4) DEFAULT NULL,
  `ratingUp` int(3) DEFAULT NULL,
  `ratingDown` int(3) DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`idComm`),
  KEY `FK_idRecord` (`idExt`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=50 ;

-- --------------------------------------------------------

--
-- Table structure for table `blogComments_commGallery`
--

CREATE TABLE IF NOT EXISTS `blogComments_commGallery` (
  `idPic` int(5) NOT NULL AUTO_INCREMENT,
  `idComm` int(5) NOT NULL,
  `picUrl` text NOT NULL,
  `picTitle` varchar(80) DEFAULT NULL,
  `picDescr` text,
  `picLoc` text COMMENT 'locatia',
  `picDate` date DEFAULT NULL,
  PRIMARY KEY (`idPic`),
  KEY `idComm` (`idComm`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `blogComments_prior`
--

CREATE TABLE IF NOT EXISTS `blogComments_prior` (
  `idComm` int(5) NOT NULL,
  `idRecord` int(5) NOT NULL,
  `priorityLevel` tinyint(4) NOT NULL,
  UNIQUE KEY `idComm_UNIQUE` (`idComm`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='priorities for comments';

-- --------------------------------------------------------

--
-- Table structure for table `blogComments_text`
--

CREATE TABLE IF NOT EXISTS `blogComments_text` (
  `idComm` int(5) NOT NULL,
  `comment` text NOT NULL,
  UNIQUE KEY `idComm` (`idComm`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `blogComments_thumbs`
--

CREATE TABLE IF NOT EXISTS `blogComments_thumbs` (
  `idComm` int(5) NOT NULL,
  `thumbsUp` int(3) DEFAULT '0',
  `thumbsDown` int(3) DEFAULT '0',
  UNIQUE KEY `idComm` (`idComm`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `blogMap_recordsTags`
--

CREATE TABLE IF NOT EXISTS `blogMap_recordsTags` (
  `idTag` int(4) NOT NULL AUTO_INCREMENT,
  `idRecord` int(5) NOT NULL,
  `tagName` varchar(50) NOT NULL,
  PRIMARY KEY (`idTag`),
  KEY `idRecord` (`idRecord`),
  KEY `idTag` (`tagName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;

-- --------------------------------------------------------

--
-- Table structure for table `blogRecords`
--

CREATE TABLE IF NOT EXISTS `blogRecords` (
  `idRecord` int(5) NOT NULL AUTO_INCREMENT,
  `idCat` int(5) DEFAULT NULL COMMENT 'ext ITEMS',
  `uidRec` int(5) DEFAULT NULL COMMENT 'userID - author',
  `entryDate` date NOT NULL COMMENT 'ultima revizie',
  `publishDate` date DEFAULT NULL COMMENT 'data publicarii',
  `nrRates` int(3) DEFAULT NULL,
  `ratingTotal` int(5) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `lead` longtext,
  `leadSec` text,
  `country` varchar(60) DEFAULT NULL,
  `city` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`idRecord`),
  KEY `idCat` (`idCat`),
  KEY `uid` (`uidRec`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1084 ;

-- --------------------------------------------------------

--
-- Table structure for table `blogRecords_prior`
--

CREATE TABLE IF NOT EXISTS `blogRecords_prior` (
  `idRecord` int(5) NOT NULL AUTO_INCREMENT,
  `priorityLevel` int(1) NOT NULL,
  `endDate` date NOT NULL,
  PRIMARY KEY (`idRecord`),
  UNIQUE KEY `idRecord` (`idRecord`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `blogRecords_settings`
--

CREATE TABLE IF NOT EXISTS `blogRecords_settings` (
  `idRecord` int(5) NOT NULL COMMENT 'ext blogRecords',
  `modelBlog_name` varchar(50) DEFAULT NULL COMMENT 'template pt acest record',
  `modelComm_name` varchar(50) DEFAULT NULL COMMENT 'modelul Commenturilor',
  `commentsView` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'se vad sau nu',
  `commentsApprov` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'commenturile trebuie aprobate inainte de publicare',
  `commentsStat` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'se mai poate sau nu posta',
  `SEO` text NOT NULL COMMENT 'vector serializat cu metauri',
  UNIQUE KEY `idRecord` (`idRecord`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `blogRecords_view`
--
CREATE TABLE IF NOT EXISTS `blogRecords_view` (
`idRecord` int(5)
,`idCat` int(5)
,`uidRec` int(5)
,`entryDate` date
,`publishDate` date
,`nrRates` int(3)
,`ratingTotal` int(5)
,`title` varchar(255)
,`content` longtext
,`lead` longtext
,`leadSec` text
,`country` varchar(60)
,`city` varchar(60)
,`modelBlog_name` varchar(50)
,`modelComm_name` varchar(50)
,`commentsView` tinyint(1)
,`commentsStat` tinyint(1)
,`commentsApprov` tinyint(1)
,`SEO` text
);
-- --------------------------------------------------------

--
-- Table structure for table `blogTags`
--

CREATE TABLE IF NOT EXISTS `blogTags` (
  `idTag` int(5) NOT NULL AUTO_INCREMENT,
  `tagName` varchar(50) NOT NULL,
  PRIMARY KEY (`idTag`),
  UNIQUE KEY `tagName` (`tagName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=346 ;

-- --------------------------------------------------------

--
-- Table structure for table `blogTags_banned`
--

CREATE TABLE IF NOT EXISTS `blogTags_banned` (
  `idTag` int(5) NOT NULL AUTO_INCREMENT,
  `tagname` varchar(50) NOT NULL,
  PRIMARY KEY (`idTag`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `blog_picManager`
--

CREATE TABLE IF NOT EXISTS `blog_picManager` (
  `idPic` int(5) NOT NULL AUTO_INCREMENT,
  `idRecord` int(5) NOT NULL,
  `picUrl` text NOT NULL,
  `picTitle` varchar(80) DEFAULT NULL,
  `picAuth` varchar(80) DEFAULT NULL COMMENT 'autor',
  `picLoc` text COMMENT 'locatia',
  `picDescr` text COMMENT 'descriere',
  `picDate` date DEFAULT NULL,
  PRIMARY KEY (`idPic`),
  KEY `idRecord` (`idRecord`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Table structure for table `rating_blog`
--

CREATE TABLE IF NOT EXISTS `rating_blog` (
  `uid` int(5) NOT NULL,
  `idRecord` int(5) NOT NULL,
  `rating` int(1) NOT NULL,
  UNIQUE KEY `uid` (`uid`,`idRecord`),
  KEY `idRecord` (`idRecord`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure for view `blogRecords_view`
--
DROP TABLE IF EXISTS `blogRecords_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ioana`@`localhost` SQL SECURITY DEFINER VIEW `blogRecords_view` AS select `blogRecords`.`idRecord` AS `idRecord`,`blogRecords`.`idCat` AS `idCat`,`blogRecords`.`uidRec` AS `uidRec`,`blogRecords`.`entryDate` AS `entryDate`,`blogRecords`.`publishDate` AS `publishDate`,`blogRecords`.`nrRates` AS `nrRates`,`blogRecords`.`ratingTotal` AS `ratingTotal`,`blogRecords`.`title` AS `title`,`blogRecords`.`content` AS `content`,`blogRecords`.`lead` AS `lead`,`blogRecords`.`leadSec` AS `leadSec`,`blogRecords`.`country` AS `country`,`blogRecords`.`city` AS `city`,`blogRecords_settings`.`modelBlog_name` AS `modelBlog_name`,`blogRecords_settings`.`modelComm_name` AS `modelComm_name`,`blogRecords_settings`.`commentsView` AS `commentsView`,`blogRecords_settings`.`commentsStat` AS `commentsStat`,`blogRecords_settings`.`commentsApprov` AS `commentsApprov`,`blogRecords_settings`.`SEO` AS `SEO` from (`blogRecords` left join `blogRecords_settings` on((`blogRecords`.`idRecord` = `blogRecords_settings`.`idRecord`)));

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blogarticles_records`
--
ALTER TABLE `blogarticles_records`
  ADD CONSTRAINT `blogarticles_records_ibfk_1` FOREIGN KEY (`idRecord`) REFERENCES `blogRecords` (`idRecord`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blogComments`
--
ALTER TABLE `blogComments`
  ADD CONSTRAINT `blogComments_ibfk_1` FOREIGN KEY (`idExt`) REFERENCES `blogRecords` (`idRecord`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blogComments_commGallery`
--
ALTER TABLE `blogComments_commGallery`
  ADD CONSTRAINT `blogComments_commGallery_ibfk_1` FOREIGN KEY (`idComm`) REFERENCES `blogComments` (`idComm`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blogComments_text`
--
ALTER TABLE `blogComments_text`
  ADD CONSTRAINT `blogComments_text_ibfk_1` FOREIGN KEY (`idComm`) REFERENCES `blogComments` (`idComm`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blogComments_thumbs`
--
ALTER TABLE `blogComments_thumbs`
  ADD CONSTRAINT `blogComments_thumbs_ibfk_1` FOREIGN KEY (`idComm`) REFERENCES `blogComments` (`idComm`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blogMap_recordsTags`
--
ALTER TABLE `blogMap_recordsTags`
  ADD CONSTRAINT `blogMap_recordsTags_ibfk_1` FOREIGN KEY (`idRecord`) REFERENCES `blogRecords` (`idRecord`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blogRecords`
--
ALTER TABLE `blogRecords`
  ADD CONSTRAINT `blogRecords_ibfk_1` FOREIGN KEY (`idCat`) REFERENCES `ITEMS` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blogRecords_prior`
--
ALTER TABLE `blogRecords_prior`
  ADD CONSTRAINT `blogRecords_prior_ibfk_1` FOREIGN KEY (`idRecord`) REFERENCES `blogRecords` (`idRecord`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blogRecords_settings`
--
ALTER TABLE `blogRecords_settings`
  ADD CONSTRAINT `blogRecords_settings_ibfk_1` FOREIGN KEY (`idRecord`) REFERENCES `blogRecords` (`idRecord`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blog_picManager`
--
ALTER TABLE `blog_picManager`
  ADD CONSTRAINT `blog_picManager_ibfk_1` FOREIGN KEY (`idRecord`) REFERENCES `blogRecords` (`idRecord`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rating_blog`
--
ALTER TABLE `rating_blog`
  ADD CONSTRAINT `rating_blog_ibfk_1` FOREIGN KEY (`idRecord`) REFERENCES `blogRecords` (`idRecord`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
