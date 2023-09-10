SET NAMES utf8;

DROP TABLE IF EXISTS `phpcore_articles`;
DROP TABLE IF EXISTS `phpcore_buttons`;
DROP TABLE IF EXISTS `phpcore_buttons_sub`;
DROP TABLE IF EXISTS `phpcore_categories`;
DROP TABLE IF EXISTS `phpcore_categories_permission`;
DROP TABLE IF EXISTS `phpcore_conversations`;
DROP TABLE IF EXISTS `phpcore_conversations_messages`;
DROP TABLE IF EXISTS `phpcore_conversations_recipients`;
DROP TABLE IF EXISTS `phpcore_deleted_content`;
DROP TABLE IF EXISTS `phpcore_forgot_password`;
DROP TABLE IF EXISTS `phpcore_forums`;
DROP TABLE IF EXISTS `phpcore_forums_permission`;
DROP TABLE IF EXISTS `phpcore_groups`;
DROP TABLE IF EXISTS `phpcore_labels`;
DROP TABLE IF EXISTS `phpcore_logs`;
DROP TABLE IF EXISTS `phpcore_notifications`;
DROP TABLE IF EXISTS `phpcore_pages`;
DROP TABLE IF EXISTS `phpcore_plugins`;
DROP TABLE IF EXISTS `phpcore_posts`;
DROP TABLE IF EXISTS `phpcore_posts_likes`;
DROP TABLE IF EXISTS `phpcore_profile_posts`;
DROP TABLE IF EXISTS `phpcore_profile_posts_comments`;
DROP TABLE IF EXISTS `phpcore_reports`;
DROP TABLE IF EXISTS `phpcore_reports_reasons`;
DROP TABLE IF EXISTS `phpcore_roles`;
DROP TABLE IF EXISTS `phpcore_settings`;
DROP TABLE IF EXISTS `phpcore_settings_url`;
DROP TABLE IF EXISTS `phpcore_sidebar`;
DROP TABLE IF EXISTS `phpcore_statistics`;
DROP TABLE IF EXISTS `phpcore_topics_labels`;
DROP TABLE IF EXISTS `phpcore_topics_labels`;
DROP TABLE IF EXISTS `phpcore_topics_likes`;
DROP TABLE IF EXISTS `phpcore_users`;
DROP TABLE IF EXISTS `phpcore_users_unread`;
DROP TABLE IF EXISTS `phpcore_users_notifications`;
DROP TABLE IF EXISTS `phpcore_verify_account`;
DROP TABLE IF EXISTS `phpcore_verify_email`;


--
-- phpcore_articles
--

CREATE TABLE IF NOT EXISTS `phpcore_articles` (
    `article_id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `article_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `article_text` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `article_url` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `article_image` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `article_views` INT NOT NULL DEFAULT '0',
    `article_sticked` TINYINT(1) NOT NULL DEFAULT '0',
    `article_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `article_edited` TINYINT(1) NOT NULL DEFAULT '0',
    `article_edited_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`article_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_articles_labels
--

CREATE TABLE IF NOT EXISTS `phpcore_articles_labels` (
    `article_id` INT NOT NULL,
    `label_id` INT NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_buttons
--

CREATE TABLE IF NOT EXISTS `phpcore_buttons` (
    `button_id` INT NULL AUTO_INCREMENT,
    `button_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `button_link` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `button_icon` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `button_dropdown` TINYINT(1) NOT NULL DEFAULT '0',
    `position_index` INT NOT NULL DEFAULT '1',
    PRIMARY KEY (`button_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_buttons` (`button_name`, `button_link`, `button_icon`, `position_index`) VALUES 
    ('Domů', '/', 'fa-solid fa-home', 2),
    ('Fórum', '/forum/', 'fa-solid fa-comments', 1);

-- --------------------------------------------------------

--
-- phpcore_buttons_sub
--

CREATE TABLE IF NOT EXISTS `phpcore_buttons_sub` (
    `button_sub_id` INT NOT NULL AUTO_INCREMENT,
    `button_id` INT NOT NULL,
    `button_sub_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `button_sub_link` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `position_index` INT NOT NULL DEFAULT '1',
    PRIMARY KEY (`button_sub_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_categories
--

CREATE TABLE IF NOT EXISTS `phpcore_categories` (
    `category_id` INT NOT NULL AUTO_INCREMENT,
    `category_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `category_description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `position_index` INT NOT NULL DEFAULT '1',
    PRIMARY KEY (`category_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_categories` (`category_name`, `category_description`) VALUES ('První kategorie', 'Popis první kategorie');

-- --------------------------------------------------------

--
-- phpcore_categories_permission
--

CREATE TABLE IF NOT EXISTS `phpcore_categories_permission` (
    `category_id` INT NOT NULL,
    `inherit_id` INT NULL,
    `permission_see` TEXT NOT NULL DEFAULT '',
    PRIMARY KEY (`category_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_categories_permission` (`category_id`, `permission_see`) VALUES (1, '*');

-- --------------------------------------------------------

--
-- phpcore_conversations
--

CREATE TABLE IF NOT EXISTS `phpcore_conversations` (
    `conversation_id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `conversation_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `conversation_text` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `conversation_url` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `conversation_messages` INT NOT NULL DEFAULT '0',
    `conversation_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `conversation_edited` TINYINT(1) NOT NULL DEFAULT '0',
    `conversation_edited_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`conversation_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_conversations_messages
--

CREATE TABLE IF NOT EXISTS `phpcore_conversations_messages` (
    `conversation_message_id` INT NOT NULL AUTO_INCREMENT,
    `conversation_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `conversation_message_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `conversation_message_text` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `conversation_message_edited` TINYINT(1) NOT NULL DEFAULT '0',
    `conversation_message_edited_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`conversation_message_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_conversations_recipients
--

CREATE TABLE IF NOT EXISTS `phpcore_conversations_recipients` (
    `conversation_id` INT NOT NULL,
    `user_id` INT NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_deleted_content
--

CREATE TABLE IF NOT EXISTS `phpcore_deleted_content` (
    `deleted_id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `deleted_type` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `deleted_type_id` INT NOT NULL,
    `deleted_type_user_id` INT NOT NULL,
    `deleted_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`deleted_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_forgot_password
--

CREATE TABLE IF NOT EXISTS `phpcore_forgot_password` (
    `user_id` INT NOT NULL,
    `forgot_code` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `forgot_code_sent` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_forums
--

CREATE TABLE IF NOT EXISTS `phpcore_forums` (
    `forum_id` INT NOT NULL AUTO_INCREMENT,
    `category_id` INT NOT NULL,
    `forum_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `forum_description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `forum_url` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `forum_link` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `forum_icon` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `forum_topics` INT NOT NULL DEFAULT '0',
    `forum_posts` INT NOT NULL DEFAULT '0',
    `forum_main` TINYINT(1) NOT NULL DEFAULT '0',
    `position_index` INT NOT NULL DEFAULT '1',
    PRIMARY KEY (`forum_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_forums` (`category_id`, `forum_id`, `forum_name`, `forum_description`, `forum_url`, `forum_icon`, `forum_main`, `forum_topics`) VALUES
    (1, 1, 'První fórum', 'Popis prvního fóra', 'prvni-forum',  'fa-solid fa-comments', 1, 1);

-- --------------------------------------------------------

--
-- phpcore_forums_permission
--

CREATE TABLE IF NOT EXISTS `phpcore_forums_permission` (
    `forum_id` INT NOT NULL,
    `inherit_id` INT NULL,
    `permission_see` TEXT NOT NULL DEFAULT '',
    `permission_post` TEXT NOT NULL DEFAULT '',
    `permission_topic` TEXT NOT NULL DEFAULT '',
    PRIMARY KEY (`forum_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_forums_permission` (`forum_id`, `permission_see`, `permission_post`, `permission_topic`) VALUES (1, '*', '1,2', '1,2');

-- --------------------------------------------------------

--
-- phpcore_groups
--

CREATE TABLE IF NOT EXISTS `phpcore_groups` (
    `group_id` INT NOT NULL AUTO_INCREMENT,
    `group_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `group_class` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `group_color` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `group_permission` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `group_index` INT NOT NULL DEFAULT '1',
    PRIMARY KEY (`group_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_groups` (`group_id`, `group_name`, `group_class`, `group_color`, `group_permission`, `group_index`) VALUES
    (1, 'Administrátor', 'administrator1', '#de4b4b', '*', 999999),
    (2, 'Uživatel', 'uzivatel2', '#555555', 'post.create,topic.create,profilepost.create', 1);

-- --------------------------------------------------------

--
-- phpcore_labels
--

CREATE TABLE IF NOT EXISTS `phpcore_labels` (
    `label_id` INT NOT NULL AUTO_INCREMENT,
    `label_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `label_class` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `label_color` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `position_index` INT NOT NULL DEFAULT '1',
    PRIMARY KEY (`label_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_logs
--

CREATE TABLE IF NOT EXISTS `phpcore_logs` (
    `log_id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `log_action` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `log_text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `log_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_notifications
--

CREATE TABLE IF NOT EXISTS `phpcore_notifications` (
    `notification_id` INT NOT NULL AUTO_INCREMENT,
    `notification_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `notification_text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `notification_type` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `notification_hidden` TINYINT(1) NOT NULL DEFAULT '1',
    `position_index` INT NOT NULL DEFAULT '1',
    PRIMARY KEY (`notification_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_pages
--

CREATE TABLE IF NOT EXISTS `phpcore_pages` (
    `page_id` INT NOT NULL AUTO_INCREMENT,
    `page_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `page_url` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    PRIMARY KEY (`page_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_plugins
--

CREATE TABLE IF NOT EXISTS `phpcore_plugins` (
    `plugin_id` INT NOT NULL AUTO_INCREMENT,
    `plugin_name_folder` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `plugin_template` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT 'Default',
    `plugin_language` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT 'cs',
    `plugin_settings` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    PRIMARY KEY (`plugin_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_posts
--

CREATE TABLE IF NOT EXISTS `phpcore_posts` (
    `post_id` INT NOT NULL AUTO_INCREMENT,
    `topic_id` INT NOT NULL,
    `forum_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `deleted_id` INT DEFAULT NULL,
    `report_id` INT DEFAULT NULL,
    `post_text` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `post_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `post_edited` TINYINT(1) NOT NULL DEFAULT '0',
    `post_edited_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`post_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_posts_likes
--

CREATE TABLE IF NOT EXISTS `phpcore_posts_likes` (
    `post_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `like_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_profile_posts
--

CREATE TABLE IF NOT EXISTS `phpcore_profile_posts` (
    `profile_post_id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `deleted_id` INT DEFAULT NULL,
    `report_id` INT DEFAULT NULL,
    `profile_id` INT NOT NULL,
    `profile_post_text` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `profile_post_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`profile_post_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_profile_posts_comments
--

CREATE TABLE IF NOT EXISTS `phpcore_profile_posts_comments` (
    `profile_post_comment_id` INT NOT NULL AUTO_INCREMENT,
    `profile_post_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `deleted_id` INT DEFAULT NULL,
    `report_id` INT DEFAULT NULL,
    `profile_id` INT NOT NULL,
    `profile_post_comment_text` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `profile_post_comment_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`profile_post_comment_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_reports
--

CREATE TABLE IF NOT EXISTS `phpcore_reports` (
    `report_id` INT NOT NULL AUTO_INCREMENT,
    `report_type` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `report_type_id` INT NOT NULL,
    `report_type_user_id` INT NOT NULL,
    `report_status` TINYINT(1) NOT NULL DEFAULT '0',
    `report_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`report_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_reports_reasons
--

CREATE TABLE IF NOT EXISTS `phpcore_reports_reasons` (
    `report_reason_id` INT NOT NULL AUTO_INCREMENT,
    `report_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `report_reason_text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `report_reason_type` TINYINT(1) NOT NULL DEFAULT '0',
    `report_reason_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`report_reason_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_roles
--

CREATE TABLE IF NOT EXISTS `phpcore_roles` (
    `role_id` INT NOT NULL AUTO_INCREMENT,
    `role_class` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `role_color` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `role_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `role_icon` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `position_index` INT NOT NULL DEFAULT '1',
    PRIMARY KEY (`role_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- `phpcore_settings`
--

CREATE TABLE IF NOT EXISTS `phpcore_settings` (
    `key` VARCHAR(225) NOT NULL,
    `value` TEXT NOT NULL,
    PRIMARY KEY(`key`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_settings` (`key`, `value`) VALUES
    ('site_name', ''),
    ('site_locale', 'cs_CZ'),
    ('site_favicon', ''),
    ('site_updated', ''),
    ('site_started', ''),
    ("site_keywords", ''),
    ('site_timezone', 'Europe/Prague'),
    ('site_language', 'cs'),
    ('site_template', 'Default'),
    ('site_description', ''),
    ('site_language_editor', 'cs'),
    ('site_allow_forgot_password', 1),
    ('site_mode', 'forum'),
    ('site_mode_blog_profiles', 1),
    ('site_mode_blog_editing', 0),
    ('site_mode_static_index', ''),
    ('site_mode_forum_index', 1),
    ('registration_terms', ''),
    ('registration_verify', 1),
    ('registration_enabled', 0),
    ('registration_key_site', ''),
    ('registration_key_secret', ''),
    ('email_prefix', 'noreply'),
    ('email_smtp_host', ''),
    ('email_smtp_port', ''),
    ('email_smtp_username', ''),
    ('email_smtp_password', ''),
    ('email_smtp_enabled', 0),
    ('image_max_size', '10400'),
    ('image_gif', 0),
    ('cookie_enabled', 0),
    ('cookie_text', ''),
    ('session', '16186128454'),
    ('session_scripts', '07618949407'),
    ('session_styles', '35129845609'),
    ('session_groups', '98763184788'),
    ('session_labels', '12896512965'),
    ('session_roles', '84275931080'),
    ('session_template', '85426984753'),
    ('default_group', '2');

-- --------------------------------------------------------

--
-- `phpcore_settings_url`
--

CREATE TABLE IF NOT EXISTS `phpcore_settings_url` (
    `settings_url_id` INT NOT NULL AUTO_INCREMENT,
    `settings_url_from` TEXT NOT NULL,
    `settings_url_to` TEXT NOT NULL,
    `settings_url_hidden` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY(`settings_url_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_categories
--

CREATE TABLE IF NOT EXISTS `phpcore_sidebar` (
    `sidebar_id` INT NOT NULL AUTO_INCREMENT,
    `sidebar_object` TEXT NOT NULL DEFAULT '',
    `position_index` INT NOT NULL DEFAULT '1',
    PRIMARY KEY (`sidebar_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_sidebar` (`sidebar_object`, `position_index`) VALUES
    ('onlineusers', 4),
    ('posts', 3),
    ('profileposts', 2),
    ('stats', 1);

-- --------------------------------------------------------

--
-- phpcore_statistics
--

CREATE TABLE IF NOT EXISTS `phpcore_statistics` (
    `key` VARCHAR(225) NOT NULL,
    `value` INT NOT NULL,
    PRIMARY KEY(`key`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_statistics` (`key`, `value`) VALUES 
    ('user_deleted', 0),
    ('post_deleted', 0),
    ('topic_deleted', 0),
    ('profile_post_deleted', 0),
    ('profile_post_comment_deleted', 0);

-- --------------------------------------------------------

--
-- phpcore_topics
--

CREATE TABLE IF NOT EXISTS `phpcore_topics` (
    `topic_id` INT NOT NULL AUTO_INCREMENT,
    `forum_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `deleted_id` INT DEFAULT NULL,
    `report_id` INT DEFAULT NULL,
    `topic_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `topic_text` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `topic_url` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `topic_image` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `topic_attachments` INT NOT NULL DEFAULT '0',
    `topic_views` INT NOT NULL DEFAULT '0',
    `topic_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `topic_posts` INT NOT NULL DEFAULT '0',
    `topic_edited_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `topic_edited` TINYINT(1) NOT NULL DEFAULT '0',
    `topic_sticked` TINYINT(1) NOT NULL DEFAULT '0',
    `topic_locked` TINYINT(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`topic_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_topics` (`user_id`, `forum_id`, `category_id`, `topic_name`, `topic_text`, `topic_url`) VALUES
    (1, 1, 1, 'První téma', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Pellentesque pretium lectus id turpis. Etiam commodo dui eget wisi. Vestibulum erat nulla, ullamcorper nec, rutrum non, nonummy ac, erat. Curabitur sagittis hendrerit ante. Proin mattis lacinia justo. Phasellus et lorem id felis nonummy placerat.', '1.prvni-tema');

-- --------------------------------------------------------

--
-- phpcore_topics_labels
--

CREATE TABLE IF NOT EXISTS `phpcore_topics_labels` (
    `topic_id` INT NOT NULL,
    `label_id` INT NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_topics_likes
--

CREATE TABLE IF NOT EXISTS `phpcore_topics_likes` (
    `topic_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `like_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_users
--

CREATE TABLE IF NOT EXISTS `phpcore_users` (
    `user_id` INT NOT NULL AUTO_INCREMENT,
    `group_id` INT NOT NULL,
    `user_name` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `user_hash` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `user_password` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `user_registered` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_email` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `user_text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `user_roles` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `user_profile_image` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `user_header_image` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `user_last_activity` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_signature` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `user_about` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `user_location` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `user_discord` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `user_facebook` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `user_instagram` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `user_age` TEXT NOT NULL DEFAULT '',
    `user_gender` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT 'undefined',
    `user_posts` INT NOT NULL DEFAULT '0',
    `user_topics` INT NOT NULL DEFAULT '0',
    `user_articles` INT NOT NULL DEFAULT '0',
    `user_reputation` INT NOT NULL DEFAULT '0',
    `user_deleted` TINYINT(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`user_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_users_notifications
--

CREATE TABLE IF NOT EXISTS `phpcore_users_notifications` (
    `user_notification_id` INT NOT NULL AUTO_INCREMENT,
    `to_user_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `user_notification_item` TEXT COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `user_notification_item_id` INT NOT NULL,
    `user_notification_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_notification_id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- phpcore_users_unread
--

CREATE TABLE IF NOT EXISTS `phpcore_users_unread` (
    `conversation_id` INT NOT NULL,
    `user_id` INT NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------


--
-- phpcore_verify_account
--

CREATE TABLE IF NOT EXISTS `phpcore_verify_account` (
    `user_id` INT NOT NULL,
    `account_code` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `account_code_sent` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------


--
-- phpcore_verify_email
--

CREATE TABLE IF NOT EXISTS `phpcore_verify_email` (
    `user_id` INT NOT NULL,
    `email_code` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `email_code_sent` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_email` TEXT COLLATE utf8_general_ci NOT NULL DEFAULT '',
    PRIMARY KEY (`user_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;