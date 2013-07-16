
--
-- Table structure for table `blogRecords`
--

CREATE TABLE IF NOT EXISTS `blogRecords` (
  `idRecord` int(5) NOT NULL AUTO_INCREMENT,
  `idCat` int(5) DEFAULT NULL COMMENT 'ext ITEMS',
  `uidRec` int(5) DEFAULT NULL COMMENT 'userID - author',
  `entryDate` date NOT NULL COMMENT 'ultima revizie',
  `publishDate` date DEFAULT NULL COMMENT 'data publicarii',
  `nrRates` int(3) DEFAULT NULL COMMENT 'DEP',
  `ratingTotal` int(5) DEFAULT NULL COMMENT 'DEP',
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `lead` longtext,
  `leadSec` text,
  `country` varchar(60) DEFAULT NULL,
  `city` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`idRecord`),
  KEY `idCat` (`idCat`),
  KEY `uid` (`uidRec`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Constraints for table `blogRecords`
--
ALTER TABLE `blogRecords`
  ADD CONSTRAINT `blogRecords_ibfk_1` FOREIGN KEY (`idCat`) REFERENCES `ITEMS` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;



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

--
-- Constraints for table `blogRecords_settings`
--
ALTER TABLE `blogRecords_settings`
  ADD CONSTRAINT `blogRecords_settings_ibfk_1` FOREIGN KEY (`idRecord`) REFERENCES `blogRecords` (`idRecord`) ON DELETE CASCADE ON UPDATE CASCADE;





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

--
-- Constraints for table `blogRecords_prior`
--
ALTER TABLE `blogRecords_prior`
  ADD CONSTRAINT `blogRecords_prior_ibfk_1`
  FOREIGN KEY (`idRecord`)
  REFERENCES `blogRecords` (`idRecord`) ON DELETE CASCADE ON UPDATE CASCADE;




--
-- ==================================[ Table structure for table `blogTags`]====
--

CREATE TABLE IF NOT EXISTS `blogTags` (
  `idTag` int(5) NOT NULL AUTO_INCREMENT,
  `tagName` varchar(50) NOT NULL,
  PRIMARY KEY (`idTag`),
  UNIQUE KEY `tagName` (`tagName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



--
-- Table structure for table `blogTags_banned`
--

CREATE TABLE IF NOT EXISTS `blogTags_banned` (
  `idTag` int(5) NOT NULL AUTO_INCREMENT,
  `tagname` varchar(50) NOT NULL,
  PRIMARY KEY (`idTag`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;



--
-- Table structure for table `blogMap_recordsTags`
--

CREATE TABLE IF NOT EXISTS `blogMap_recordsTags` (
  `idRecord` int(5) NOT NULL,
  `tagName` varchar(50) NOT NULL,
  KEY `idRecord` (`idRecord`),
  KEY `idTag` (`tagName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for table `blogMap_recordsTags`
--
ALTER TABLE `blogMap_recordsTags`
  ADD CONSTRAINT `blogMap_recordsTags_ibfk_1` FOREIGN KEY (`idRecord`)
   REFERENCES `blogRecords` (`idRecord`) ON DELETE CASCADE ON UPDATE CASCADE;





-- ============================================[ view - blogRecords_view ]======
--
-- VIEW blogRecords_view = blogRecords + blogRecords_settings
--

CREATE OR REPLACE VIEW `blogRecords_view`
AS select
`blogRecords`.`idRecord` AS `idRecord`,
`blogRecords`.`idCat` AS `idCat`,
`blogRecords`.`uidRec` AS `uidRec`,
`blogRecords`.`entryDate` AS `entryDate`,
`blogRecords`.`publishDate` AS `publishDate`,
`blogRecords`.`nrRates` AS `nrRates`,
`blogRecords`.`ratingTotal` AS `ratingTotal`,
`blogRecords`.`title` AS `title`,
`blogRecords`.`content` AS `content`,
`blogRecords`.`lead` AS `lead`,
`blogRecords`.`leadSec` AS `leadSec`,
`blogRecords`.`country` AS `country`,
`blogRecords`.`city` AS `city`,
`blogRecords_settings`.`modelBlog_name` AS `modelBlog_name`,
`blogRecords_settings`.`modelComm_name` AS `modelComm_name`,
`blogRecords_settings`.`commentsView` AS `commentsView`,
`blogRecords_settings`.`commentsStat` AS `commentsStat`,
`blogRecords_settings`.`commentsApprov` AS `commentsApprov`,
`blogRecords_settings`.`SEO` AS `SEO`
from (`blogRecords` left join `blogRecords_settings`
 on((`blogRecords`.`idRecord` = `blogRecords_settings`.`idRecord`)));
