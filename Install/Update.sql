CREATE TABLE IF NOT EXISTS `phpcore_plugins` (
    `plugin_id` INT NOT NULL AUTO_INCREMENT,
    `plugin_name_folder` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT "",
    PRIMARY KEY (`plugin_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `phpcore_buttons_sub` ADD COLUMN IF NOT EXISTS `button_sub_link_type` TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE `phpcore_buttons_sub` DROP COLUMN IF EXISTS `is_external_link`;
ALTER TABLE `phpcore_buttons_sub` DROP COLUMN IF EXISTS `page_id`;

ALTER TABLE `phpcore_buttons` ADD COLUMN IF NOT EXISTS button_link_type TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE `phpcore_buttons` DROP COLUMN IF EXISTS is_external_link;
ALTER TABLE `phpcore_buttons` DROP COLUMN IF EXISTS page_id;

ALTER TABLE `phpcore_users` MODIFY user_signature TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT "";

CREATE TABLE IF NOT EXISTS `phpcore_statistics` (
    `key` VARCHAR(225) NOT NULL,
    `value` INT NOT NULL,
    PRIMARY KEY(`key`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT IGNORE INTO `phpcore_statistics` (`key`, `value`) VALUES 
    ("user_deleted", 0),
    ("post_deleted", 0),
    ("topic_deleted", 0),
    ("profile_post_deleted", 0),
    ("profile_post_comment_deleted", 0);


CREATE TABLE IF NOT EXISTS `phpcore_settings` (
    `key` VARCHAR(225) NOT NULL,
    `value` TEXT NOT NULL,
    PRIMARY KEY(`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `phpcore_settings` (`key`, `value`) VALUES
    ("site.name", ""),
    ("site.locale", ""),
    ("site.version", ""),
    ("site.favicon", ""),
    ("site.updated", ""),
    ("site.started", ""),
    ("site.keywords", ""),
    ("site.timezone", ""),
    ("site.language", ""),
    ("site.template", ""),
    ("site.description", ""),
    ("site.language_editor", ""),
    ("site.background_image", ""),
    ("site.background_image_position", ""),
    ("registration.terms", ""),
    ("registration.enabled", ""),
    ("registration.key_site", ""),
    ("registration.key_secret", ""),
    ("email.prefix", ""),
    ("email.smtp_host", ""),
    ("email.smtp_port", ""),
    ("email.smtp_username", ""),
    ("email.smtp_password", ""),
    ("email.smtp_enabled", ""),
    ("image.max_size", ""),
    ("cookie.enabled", ""),
    ("cookie.text", ""),
    ("session", ""),
    ('session.scripts', '07618949407'),
    ('session.styles', '35129845609'),
    ('session.groups', '98763184788'),
    ('session.labels', '12896512965'),
    ("default_group", "");


CREATE TABLE IF NOT EXISTS `phpcore_settings_url` (
    `settings_url_id` INT NOT NULL AUTO_INCREMENT,
    `settings_url_from` TEXT NOT NULL,
    `settings_url_to` TEXT NOT NULL,
    `settings_url_hidden` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY(`settings_url_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


UPDATE `phpcore_logs` SET `log_action` = CONCAT('/', log_action);

-- Buttons
ALTER IGNORE TABLE `phpcore_buttons` DROP COLUMN IF EXISTS `is_dropdown`;
ALTER IGNORE TABLE `phpcore_buttons` ADD COLUMN IF NOT EXISTS `button_dropdown` TINYINT(1) DEFAULT 0;


-- Forums
ALTER IGNORE TABLE `phpcore_forums` DROP COLUMN IF EXISTS `is_main`;
ALTER IGNORE TABLE `phpcore_forums` ADD COLUMN IF NOT EXISTS `forum_main` TINYINT(1) DEFAULT 0;


-- Conversations
ALTER IGNORE TABLE `phpcore_conversations` DROP COLUMN IF EXISTS `conversation_edited`;
ALTER IGNORE TABLE `phpcore_conversations` ADD COLUMN IF NOT EXISTS `conversation_edited_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER IGNORE TABLE `phpcore_conversations` DROP COLUMN IF EXISTS `is_edited`;
ALTER IGNORE TABLE `phpcore_conversations` ADD COLUMN IF NOT EXISTS `conversation_edited` TINYINT(1) DEFAULT 0;


-- Conversation messages
ALTER IGNORE TABLE `phpcore_conversations_messages` DROP COLUMN IF EXISTS `conversation_message_edited`;
ALTER IGNORE TABLE `phpcore_conversations_messages` ADD COLUMN IF NOT EXISTS `conversation_message_edited_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER IGNORE TABLE `phpcore_conversations_messages` DROP COLUMN IF EXISTS `is_edited`;
ALTER IGNORE TABLE `phpcore_conversations_messages` ADD COLUMN IF NOT EXISTS `conversation_message_edited` TINYINT(1) DEFAULT 0;


-- Posts
ALTER IGNORE TABLE `phpcore_posts` DROP COLUMN IF EXISTS `post_edited`;
ALTER IGNORE TABLE `phpcore_posts` ADD COLUMN IF NOT EXISTS `post_edited_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER IGNORE TABLE `phpcore_posts` DROP COLUMN IF EXISTS `is_edited`;
ALTER IGNORE TABLE `phpcore_posts` ADD COLUMN IF NOT EXISTS `post_edited` TINYINT(1) DEFAULT 0;


-- Topics
ALTER IGNORE TABLE `phpcore_topics` DROP COLUMN IF EXISTS `topic_edited`;
ALTER IGNORE TABLE `phpcore_topics` ADD COLUMN IF NOT EXISTS `topic_edited_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER IGNORE TABLE `phpcore_topics` DROP COLUMN IF EXISTS `is_edited`;
ALTER IGNORE TABLE `phpcore_topics` ADD COLUMN IF NOT EXISTS `topic_edited` TINYINT(1) DEFAULT 0;

ALTER IGNORE TABLE `phpcore_topics` CHANGE COLUMN IF EXISTS `is_locked` `topic_locked` TINYINT(1) DEFAULT 0;
ALTER IGNORE TABLE `phpcore_topics` CHANGE COLUMN IF EXISTS `is_sticky` `topic_sticked` TINYINT(1) DEFAULT 0;


-- Users
ALTER IGNORE TABLE `phpcore_users` CHANGE COLUMN IF EXISTS `is_deleted` `user_deleted` TINYINT(1) DEFAULT 0;
ALTER IGNORE TABLE `phpcore_users` CHANGE COLUMN IF EXISTS `is_admin` `user_admin` TINYINT(1) DEFAULT 0;


-- User Notification
ALTER IGNORE TABLE `phpcore_notifications` CHANGE COLUMN IF EXISTS `is_hidden` `notification_hidden` TINYINT(1) DEFAULT 0;

-- Groups
ALTER IGNORE TABLE `phpcore_groups` CHANGE COLUMN IF EXISTS `group_permission` `group_permission` TEXT DEFAULT "";

-- Notifications
ALTER IGNORE TABLE `phpcore_notifications` CHANGE COLUMN IF EXISTS `notification_text` `notification_text` TEXT DEFAULT "";

