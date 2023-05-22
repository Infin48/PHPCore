INSERT IGNORE INTO `phpcore_settings` (`key`, `value`) VALUES ('session.template', 4219177810);
INSERT IGNORE INTO `phpcore_settings` (`key`, `value`) VALUES ('session.roles', 14987025637);
INSERT IGNORE INTO `phpcore_settings` (`key`, `value`) VALUES ('image.gif', 0);
INSERT IGNORE INTO `phpcore_settings` (`key`, `value`) VALUES ('site.mode', 'forum');
INSERT IGNORE INTO `phpcore_settings` (`key`, `value`) VALUES ('site.mode.blog.profiles', 1);
INSERT IGNORE INTO `phpcore_settings` (`key`, `value`) VALUES ('site.mode.blog.editing', 0);
INSERT IGNORE INTO `phpcore_settings` (`key`, `value`) VALUES ('site.mode.allow_forgot_password', 1);
INSERT IGNORE INTO `phpcore_settings` (`key`, `value`) VALUES ('registration.verify', 1);

UPDATE IGNORE `phpcore_settings` SET `key` = 'email.smtp.enabled' WHERE `key` = 'email.smtp_enabled';
UPDATE IGNORE `phpcore_settings` SET `key` = 'email.smtp.host' WHERE `key` = 'email.smtp_host';
UPDATE IGNORE `phpcore_settings` SET `key` = 'email.smtp.password' WHERE `key` = 'email.smtp_password';
UPDATE IGNORE `phpcore_settings` SET `key` = 'email.smtp.port' WHERE `key` = 'email.smtp_port';
UPDATE IGNORE `phpcore_settings` SET `key` = 'email.smtp.username' WHERE `key` = 'email.smtp_username';

DELETE FROM `phpcore_settings` WHERE `key` = 'site.background_image';
DELETE FROM `phpcore_settings` WHERE `key` = 'site.background_image_position';

DROP TABLE IF EXISTS `phpcore_forums_permission_see`;
DROP TABLE IF EXISTS `phpcore_forums_permission_post`;
DROP TABLE IF EXISTS `phpcore_forums_permission_topic`;
DROP TABLE IF EXISTS `phpcore_categories_permission_see`;

/* RENAME user_notification_type?_id TO user_notification_item?_id */
ALTER TABLE `phpcore_users_notifications` CHANGE COLUMN IF EXISTS `user_notification_type` `user_notification_item` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `phpcore_users_notifications` CHANGE COLUMN IF EXISTS `user_notification_type_id` `user_notification_item_id` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '';

/* ADD phpcore_article_labels */
CREATE TABLE IF NOT EXISTS `phpcore_articles_labels` (
    `article_id` INT NOT NULL,
    `label_id` INT NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/* CLEAR phpcore_logs */
TRUNCATE `phpcore_logs`;

/* CHANGE user_age */
ALTER TABLE `phpcore_users` MODIFY `user_age` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';

/* ADD user_about */
ALTER TABLE `phpcore_users` ADD IF NOT EXISTS `user_about` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';

/* ADD article_sticked */
ALTER TABLE `phpcore_articles` ADD IF NOT EXISTS `article_sticked` TINYINT(1) NOT NULL DEFAULT '0';

/* ADD plugin_template, plugin_language AND phpcore_plugins */
ALTER TABLE `phpcore_plugins` ADD IF NOT EXISTS `plugin_template` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'Default';
ALTER TABLE `phpcore_plugins` ADD IF NOT EXISTS `plugin_language` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'cs';
ALTER TABLE `phpcore_plugins` ADD IF NOT EXISTS `plugin_settings` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}';
ALTER TABLE `phpcore_topics` ADD IF NOT EXISTS `topic_attachments` INT NOT NULL DEFAULT '0';

/* Change VERSION */
UPDATE `phpcore_settings` SET `value` = '2.0.0' WHERE `key` = 'site.version';

/* RELEASE GROUP_ID = 1 */
UPDATE IGNORE `phpcore_groups` g
LEFT JOIN (
    SELECT COUNT(*) as count
    FROM `phpcore_groups`
) number ON 1 = 1
LEFT JOIN `phpcore_users` `u` ON `u`.`group_id` = `g`.`group_id` = 1
SET `g`.`group_id` = number.count, `u`.`group_id` = number.count
WHERE `g`.`group_id` = 1;

UPDATE IGNORE `phpcore_groups` `g`
LEFT JOIN `phpcore_users` `u` ON `u`.`user_admin` = 1 AND `u`.`group_id` = `g`.`group_id`
SET `g`.`group_index` = '999999', `g`.`group_color` = '#de4b4b', `g`.`group_permission` = '*', `g`.`group_id` = 1, `u`.`group_id` = 1
WHERE `g`.`group_id` = `u`.`group_id`;

/* DROP user_admin */
ALTER TABLE `phpcore_users` DROP COLUMN IF EXISTS `user_admin`;

/* DROP button_link_type */
ALTER TABLE `phpcore_buttons` DROP COLUMN IF EXISTS `button_link_type`;

/* DROP button_sub_link_type */
ALTER TABLE `phpcore_buttons_sub` DROP COLUMN IF EXISTS `button_sub_link_type`;

/* ADD account_code_sent */
ALTER TABLE `phpcore_verify_account` ADD IF NOT EXISTS `account_code_sent` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

/* ADD forgot_code_sent */
ALTER TABLE `phpcore_forgot_password` ADD IF NOT EXISTS `forgot_code_sent` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

/* ADD email_code_sent */
ALTER TABLE `phpcore_verify_email` ADD IF NOT EXISTS `email_code_sent` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `email_code`;

/* ADD user_roles */
ALTER TABLE `phpcore_users` ADD IF NOT EXISTS `user_roles` TEXT NOT NULL DEFAULT '' AFTER `user_text`;

ALTER TABLE `phpcore_users` ADD IF NOT EXISTS `user_instagram` TEXT NOT NULL DEFAULT '' AFTER `user_location`;
ALTER TABLE `phpcore_users` ADD IF NOT EXISTS `user_facebook` TEXT NOT NULL DEFAULT '' AFTER `user_location`;
ALTER TABLE `phpcore_users` ADD IF NOT EXISTS `user_discord` TEXT NOT NULL DEFAULT '' AFTER `user_location`;

/* RENAME group_class_name TO group_class */
ALTER TABLE `phpcore_groups` CHANGE COLUMN IF EXISTS `group_class_name` `group_class` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '';

/* RENAME label_class_name TO label_class */
ALTER TABLE `phpcore_labels` CHANGE COLUMN IF EXISTS `label_class_name` `label_class` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '';

CREATE TABLE IF NOT EXISTS `phpcore_roles` (
    `role_id` INT NOT NULL AUTO_INCREMENT,
    `role_class` TEXT NOT NULL DEFAULT '',
    `role_color` TEXT NOT NULL DEFAULT '',
    `role_name` TEXT NOT NULL DEFAULT '',
    `role_icon` TEXT NOT NULL DEFAULT '',
    `position_index` TINYINT NOT NULL DEFAULT '1',
    PRIMARY KEY (`role_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/* CHANGE notification_type */
ALTER TABLE `phpcore_notifications` MODIFY `notification_type` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';

/* DELETE button_icon_style */
ALTER TABLE `phpcore_buttons` ADD IF NOT EXISTS `button_icon_style` TEXT NOT NULL;
UPDATE `phpcore_buttons` SET `button_icon` = CONCAT(`button_icon_style`, ' fa-', `button_icon`) WHERE `button_icon_style` <> '';
ALTER TABLE `phpcore_buttons` DROP `button_icon_style`;

/* DELETE forum_icon_style */
ALTER TABLE `phpcore_forums` ADD IF NOT EXISTS `forum_icon_style` TEXT NOT NULL;
UPDATE `phpcore_forums` SET `forum_icon` = CONCAT(`forum_icon_style`, ' fa-', `forum_icon`) WHERE `forum_icon_style` <> '';
ALTER TABLE `phpcore_forums` DROP `forum_icon_style`;

/* phpcore_categories_permission */
CREATE TABLE IF NOT EXISTS `phpcore_categories_permission` (
    `category_id` INT NOT NULL,
    `inherit_id` INT NULL,
    `permission_see` TEXT NOT NULL DEFAULT '',
    PRIMARY KEY (`category_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/* phpcore_forums_permission */
CREATE TABLE IF NOT EXISTS `phpcore_forums_permission` (
    `forum_id` INT NOT NULL,
    `inherit_id` INT NULL,
    `permission_see` TEXT NOT NULL DEFAULT '',
    `permission_post` TEXT NOT NULL DEFAULT '',
    `permission_topic` TEXT NOT NULL DEFAULT '',
    PRIMARY KEY (`forum_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/* phpcore_sidebar */
CREATE TABLE IF NOT EXISTS `phpcore_sidebar` (
    `sidebar_id` INT NOT NULL AUTO_INCREMENT,
    `sidebar_object` TEXT NOT NULL DEFAULT '',
    `position_index` INT NOT NULL DEFAULT '1',
    PRIMARY KEY (`sidebar_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT IGNORE INTO `phpcore_sidebar` (`sidebar_id`, `sidebar_object`, `position_index`) VALUES
    (1, 'onlineusers', 4),
    (2, 'posts', 3),
    (3, 'profileposts', 2),
    (4, 'stats', 1);