CREATE TABLE IF NOT EXISTS `news` (
  `idNw` int(3) NOT NULL AUTO_INCREMENT,
  `dateNews` dateNews DEFAULT NULL,
  `picUrl` text,
  `extLink` text,
  PRIMARY KEY (`idNw`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `news_i18n` (
  `idTr` int(4) NOT NULL AUTO_INCREMENT,
  `idNw` int(4) NOT NULL,
  `idLg` varchar(2) NOT NULL,
  `title` varchar(200) NOT NULL,
  `lead` text NOT NULL,
  `content` text,
  PRIMARY KEY (`idTr`),
  UNIQUE KEY `idNw_2` (`idNw`,`idLg`),
  KEY `idNw` (`idNw`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- Constraints for table `news_i18n`
--
ALTER TABLE `news_i18n`
  ADD CONSTRAINT `news_i18n_ibfk_1` FOREIGN KEY (`idNw`) REFERENCES `news` (`idNw`) ON DELETE CASCADE ON UPDATE CASCADE;


CREATE OR REPLACE VIEW `vw_news_i18n`
AS select
`news`.`idNw` AS `idNw`,
`news_i18n`.`idLg` AS `idLg`,
`news`.`dateNews` AS `dateNews`,
`news`.`picUrl` AS `picUrl`,
`news`.`extLink` AS `extLink`,
`news_i18n`.`title` AS `title`,
`news_i18n`.`lead` AS `lead`,
`news_i18n`.`content` AS `content`
from (`news` left join `news_i18n` on((`news`.`idNw` = `news_i18n`.`idNw`)));
