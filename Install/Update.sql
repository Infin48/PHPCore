INSERT IGNORE INTO `phpcore_settings` (`key`, `value`) VALUES ('site_mode_forum_index', 1);

UPDATE `phpcore_settings` SET `key` = REPLACE(`key`, '.', '_') WHERE `key` LIKE '%.%';