ALTER TABLE  `blogRecord_folders` CHANGE  `folderName`  `folderName` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;

ALTER TABLE  `blogRecord_folders` DROP FOREIGN KEY  `blogRecord_folders_ibfk_2` ;

ALTER TABLE  `blogRecord_folders` ADD CONSTRAINT  `blogRecord_folders_ibfk_2` FOREIGN KEY (  `idTmpl` ) REFERENCES  `blacksea_dev`.`blogRecord_tmplFiles` (
`idTmpl`
) ON DELETE RESTRICT ON UPDATE NO ACTION ;

ALTER TABLE  `blogRecord_folders` DROP FOREIGN KEY  `blogRecord_folders_ibfk_1` ;

ALTER TABLE  `blogRecord_folders` ADD CONSTRAINT  `blogRecord_folders_ibfk_1` FOREIGN KEY (  `parentFolder` ) REFERENCES  `blacksea_dev`.`blogRecord_folders` (
`idFolder`
) ON DELETE CASCADE ON UPDATE NO ACTION ;

ALTER TABLE  `blogRecord_folders` DROP FOREIGN KEY  `blogRecord_folders_ibfk_1` ;

ALTER TABLE  `blogRecords_settings` DROP FOREIGN KEY  `blogRecords_settings_ibfk_2` ;

ALTER TABLE  `blogRecords_settings` ADD CONSTRAINT  `blogRecords_settings_ibfk_2` FOREIGN KEY (  `idTmpl` ) REFERENCES  `blacksea_dev`.`blogRecord_tmplFiles` (
`idTmpl`
) ON DELETE SET NULL ON UPDATE SET NULL ;

ALTER TABLE  `blogRecords_settings` DROP FOREIGN KEY  `blogRecords_settings_ibfk_3` ;

ALTER TABLE  `blogRecords_settings` ADD CONSTRAINT  `blogRecords_settings_ibfk_3` FOREIGN KEY (  `idFolder` ) REFERENCES  `blacksea_dev`.`blogRecord_folders` (
`idFolder`
) ON DELETE SET NULL ON UPDATE SET NULL ;

ALTER TABLE  `blogRecord_folders` DROP FOREIGN KEY  `blogRecord_folders_ibfk_2` ;

ALTER TABLE  `blogRecord_folders` ADD CONSTRAINT  `blogRecord_folders_ibfk_2` FOREIGN KEY (  `idTmpl` ) REFERENCES  `blacksea_dev`.`blogRecord_tmplFiles` (
`idTmpl`
) ON DELETE SET NULL ON UPDATE CASCADE ;

ALTER TABLE  `blogRecords_settings` DROP FOREIGN KEY  `blogRecords_settings_ibfk_2` ;

ALTER TABLE  `blogRecords_settings` ADD CONSTRAINT  `blogRecords_settings_ibfk_2` FOREIGN KEY (  `idTmpl` ) REFERENCES  `blacksea_dev`.`blogRecord_tmplFiles` (
`idTmpl`
) ON DELETE SET NULL ON UPDATE CASCADE ;

ALTER TABLE  `blogRecords_settings` DROP FOREIGN KEY  `blogRecords_settings_ibfk_3` ;

ALTER TABLE  `blogRecords_settings` ADD CONSTRAINT  `blogRecords_settings_ibfk_3` FOREIGN KEY (  `idFolder` ) REFERENCES  `blacksea_dev`.`blogRecord_folders` (
`idFolder`
) ON DELETE SET NULL ON UPDATE CASCADE ;

ALTER TABLE  `auth_permissions_blog` ADD  `article_tmpl` INT NULL DEFAULT NULL AFTER  `article_edit` ;

CREATE OR REPLACE VIEW `cid_permissions` AS select `auth_classes`.`cid` AS `cid`,`auth_classes`.`name` AS `name`,`auth_classes`.`parent` AS `parent`,`auth_permissions_blog`.`article_add` AS `article_add`,
`auth_permissions_blog`.`article_edit` AS `article_edit`,
`auth_permissions_blog`.`article_tmpl` AS `article_tmpl`,

`auth_permissions_blog`.`article_pub` AS `article_pub`,`auth_permissions_blog`.`comm_edit` AS `comm_edit`,`auth_permissions_blog`.`comm_add` AS `comm_add`,`auth_permissions_site`.`page_addRm` AS `page_addRm`,`auth_permissions_site`.`page_edit` AS `page_edit`,`auth_permissions_site`.`page_pub` AS `page_pub`,`auth_permissions_sys`.`user_addRm` AS `user_addRm`,`auth_permissions_sys`.`user_edit` AS `user_edit`,`auth_permissions_sys`.`user_mute` AS `user_mute`,`auth_permissions_sys`.`perm_manage` AS `perm_manage`,`auth_permissions_sys`.`class_manage` AS `class_manage` from (((`auth_classes` join `auth_permissions_blog` on((`auth_classes`.`cid` = `auth_permissions_blog`.`cid`))) join `auth_permissions_site` on((`auth_classes`.`cid` = `auth_permissions_site`.`cid`))) join `auth_permissions_sys` on((`auth_classes`.`cid` = `auth_permissions_sys`.`cid`)));

INSERT INTO  `blacksea_dev`.`auth_classes` (
`cid` ,
`name` ,
`parent`
)
VALUES (
NULL ,  'designer', NULL
);


INSERT INTO  `blacksea_dev`.`auth_permissions_blog` (
`cid` ,
`article_add` ,
`article_edit` ,
`article_tmpl` ,
`article_pub` ,
`comm_edit` ,
`comm_add`
)
VALUES (
'7',  NULL,  '1',  '1', NULL , NULL , NULL
);


RENAME TABLE blogRecord_types TO blogRecord_formats;

ALTER TABLE  `blogRecord_formats` CHANGE  `idType`  `idFormat` INT( 2 ) NOT NULL AUTO_INCREMENT ,
CHANGE  `type`  `format` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;

UPDATE  `blogRecords_settings` SET modelBlog_name =1 WHERE  `modelBlog_name` =  'story';
UPDATE  `blogRecords_settings` SET modelBlog_name =2 WHERE  `modelBlog_name` =  'photo';
UPDATE  `blogRecords_settings` SET modelBlog_name =3 WHERE  `modelBlog_name` =  'multimedia';

ALTER TABLE  `blogRecords_settings` CHANGE  `modelBlog_name`  `idFormat` INT( 5 ) NULL DEFAULT NULL COMMENT  'id format';

ALTER TABLE  `blogRecords_settings` ADD INDEX (  `idFormat` ) ;

ALTER TABLE  `blogRecords_settings` ADD FOREIGN KEY (  `idFormat` ) REFERENCES  `blacksea_dev`.`blogRecord_formats` (
`idFormat`
) ON DELETE SET NULL ON UPDATE SET NULL ;

ALTER TABLE  `blogRecords_settings` DROP FOREIGN KEY  `blogRecords_settings_ibfk_3` ;

ALTER TABLE  `blogRecords_settings` ADD CONSTRAINT  `blogRecords_settings_ibfk_3` FOREIGN KEY (  `idFolder` ) REFERENCES `blacksea_dev`.`blogRecord_folders` (
`idFolder`
) ON DELETE SET NULL ON UPDATE NO ACTION ;

ALTER TABLE  `blogRecords_settings` DROP FOREIGN KEY  `blogRecords_settings_ibfk_3` ;

ALTER TABLE  `blogRecords_settings` ADD CONSTRAINT  `blogRecords_settings_ibfk_3` FOREIGN KEY (  `idFolder` ) REFERENCES `blacksea_dev`.`blogRecord_folders` (
`idFolder`
) ON DELETE NO ACTION ON UPDATE NO ACTION ;

ALTER TABLE  `blogRecords_settings` DROP FOREIGN KEY  `blogRecords_settings_ibfk_3` ;
