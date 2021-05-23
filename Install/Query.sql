SET NAMES utf8;

DROP TABLE IF EXISTS `phpcore_buttons`;
DROP TABLE IF EXISTS `phpcore_buttons_sub`;
DROP TABLE IF EXISTS `phpcore_categories`;
DROP TABLE IF EXISTS `phpcore_categories_permission_see`;
DROP TABLE IF EXISTS `phpcore_conversations`;
DROP TABLE IF EXISTS `phpcore_conversations_messages`;
DROP TABLE IF EXISTS `phpcore_conversations_recipients`;
DROP TABLE IF EXISTS `phpcore_deleted_content`;
DROP TABLE IF EXISTS `phpcore_forgot_password`;
DROP TABLE IF EXISTS `phpcore_forums`;
DROP TABLE IF EXISTS `phpcore_forums_permission_post`;
DROP TABLE IF EXISTS `phpcore_forums_permission_see`;
DROP TABLE IF EXISTS `phpcore_forums_permission_topic`;
DROP TABLE IF EXISTS `phpcore_groups`;
DROP TABLE IF EXISTS `phpcore_labels`;
DROP TABLE IF EXISTS `phpcore_logs`;
DROP TABLE IF EXISTS `phpcore_notifications`;
DROP TABLE IF EXISTS `phpcore_pages`;
DROP TABLE IF EXISTS `phpcore_posts`;
DROP TABLE IF EXISTS `phpcore_posts_likes`;
DROP TABLE IF EXISTS `phpcore_profile_posts`;
DROP TABLE IF EXISTS `phpcore_profile_posts_comments`;
DROP TABLE IF EXISTS `phpcore_reports`;
DROP TABLE IF EXISTS `phpcore_reports_reasons`;
DROP TABLE IF EXISTS `phpcore_topics`;
DROP TABLE IF EXISTS `phpcore_topics_labels`;
DROP TABLE IF EXISTS `phpcore_topics_likes`;
DROP TABLE IF EXISTS `phpcore_users`;
DROP TABLE IF EXISTS `phpcore_users_unread`;
DROP TABLE IF EXISTS `phpcore_users_notifications`;
DROP TABLE IF EXISTS `phpcore_verify_account`;
DROP TABLE IF EXISTS `phpcore_verify_email`;


--
-- phpcore_buttons
--

CREATE TABLE IF NOT EXISTS `phpcore_buttons` (
  `button_id` bigint(11) NULL AUTO_INCREMENT,
  `page_id` bigint(11) DEFAULT NULL,
  `button_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `button_link` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `button_icon` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `button_icon_style` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `is_external_link` tinyint(4) NOT NULL DEFAULT '0',
  `is_dropdown` tinyint(4) NOT NULL DEFAULT '0',
  `position_index` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`button_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_buttons_sub
--

CREATE TABLE IF NOT EXISTS `phpcore_buttons_sub` (
  `button_sub_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `button_id` bigint(11) NOT NULL,
  `page_id` bigint(11) DEFAULT NULL,
  `button_sub_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `button_sub_link` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `is_external_link` tinyint(4) NOT NULL DEFAULT '0',
  `position_index` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`button_sub_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_categories
--

CREATE TABLE IF NOT EXISTS `phpcore_categories` (
  `category_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `category_description` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `position_index` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`category_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_categories` (`category_name`, `category_description`) VALUES ('První kategorie', 'Popis první kategorie');

-- --------------------------------------------------------

--
-- phpcore_categories_permission_see
--

CREATE TABLE IF NOT EXISTS `phpcore_categories_permission_see` (
  `category_id` bigint(11) NOT NULL,
  `group_id` bigint(11) NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_categories_permission_see` (`category_id`, `group_id`) VALUES (1, 0), (1, 1), (1, 2);

-- --------------------------------------------------------

--
-- phpcore_conversations
--

CREATE TABLE IF NOT EXISTS `phpcore_conversations` (
  `conversation_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(11) NOT NULL,
  `conversation_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `conversation_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `conversation_url` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `conversation_messages` int(11) NOT NULL DEFAULT '0',
  `conversation_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_edited` tinyint(4) NOT NULL DEFAULT '0',
  `conversation_edited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`conversation_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_conversations_messages
--

CREATE TABLE IF NOT EXISTS `phpcore_conversations_messages` (
  `conversation_message_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(11) NOT NULL,
  `user_id` bigint(11) NOT NULL,
  `conversation_message_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `conversation_message_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `is_edited` tinyint(1) NOT NULL DEFAULT '0',
  `conversation_message_edited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`conversation_message_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_conversations_recipients
--

CREATE TABLE IF NOT EXISTS `phpcore_conversations_recipients` (
  `conversation_id` bigint(11) NOT NULL,
  `user_id` bigint(11) NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_deleted_content
--

CREATE TABLE IF NOT EXISTS `phpcore_deleted_content` (
  `deleted_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(11) NOT NULL,
  `deleted_type` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `deleted_type_id` bigint(11) NOT NULL,
  `deleted_type_user_id` bigint(11) NOT NULL,
  `deleted_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`deleted_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_forgot_password
--

CREATE TABLE IF NOT EXISTS `phpcore_forgot_password` (
  `user_id` bigint(11) NOT NULL,
  `forgot_code` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_forums
--

CREATE TABLE IF NOT EXISTS `phpcore_forums` (
  `forum_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `category_id` bigint(11) NOT NULL,
  `forum_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `forum_description` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `forum_url` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `forum_link` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `forum_icon` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `forum_icon_style` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `forum_topics` mediumint(11) NOT NULL DEFAULT '0',
  `forum_posts` mediumint(11) NOT NULL DEFAULT '0',
  `is_main` tinyint(1) NOT NULL DEFAULT '0',
  `position_index` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`forum_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_forums` (`category_id`, `forum_name`, `forum_description`, `forum_url`, `forum_icon`, `forum_icon_style`, `is_main`, `forum_topics`) VALUES
(1, 'První fórum', 'Popis prvního fóra', 'prvni-forum',  'comments', 'fas', 1, 1);

-- --------------------------------------------------------

--
-- phpcore_forums_permission_post
--

CREATE TABLE IF NOT EXISTS `phpcore_forums_permission_post` (
  `forum_id` bigint(11) NOT NULL,
  `group_id` bigint(11) NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_forums_permission_post` (`forum_id`, `group_id`) VALUES (1, 1), (1, 2);

-- --------------------------------------------------------

--
-- phpcore_forums_permission_see
--

CREATE TABLE IF NOT EXISTS `phpcore_forums_permission_see` (
  `forum_id` bigint(11) NOT NULL,
  `group_id` bigint(11) NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_forums_permission_see` (`forum_id`, `group_id`) VALUES (1, 0), (1, 1), (1, 2);

-- --------------------------------------------------------

--
-- phpcore_forums_permission_topic
--

CREATE TABLE IF NOT EXISTS `phpcore_forums_permission_topic` (
  `forum_id` bigint(11) NOT NULL,
  `group_id` bigint(11) NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_forums_permission_topic` (`forum_id`, `group_id`) VALUES (1, 1), (1, 2);

-- --------------------------------------------------------

--
-- phpcore_groups
--

CREATE TABLE IF NOT EXISTS `phpcore_groups` (
  `group_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `group_class_name` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `group_color` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `group_permission` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `group_index` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`group_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_groups` (`group_name`, `group_class_name`, `group_color`, `group_permission`, `group_index`) VALUES
('Uživatel', 'uzivatel1', '#555555', 'post.create,topic.create,profilepost.create', 1),
('Administrátor', 'administrator2', '#3174d7', '*', 2);

-- --------------------------------------------------------

--
-- phpcore_labels
--

CREATE TABLE IF NOT EXISTS `phpcore_labels` (
  `label_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `label_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `label_class_name` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `label_color` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `position_index` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`label_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_logs
--

CREATE TABLE IF NOT EXISTS `phpcore_logs` (
  `log_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(11) NOT NULL,
  `log_action` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `log_text` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `log_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_notifications
--

CREATE TABLE IF NOT EXISTS `phpcore_notifications` (
  `notification_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `notification_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `notification_text` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `notification_type` int(11) NOT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT '1',
  `position_index` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`notification_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_pages
--

CREATE TABLE IF NOT EXISTS `phpcore_pages` (
  `page_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `page_url` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`page_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_posts
--

CREATE TABLE IF NOT EXISTS `phpcore_posts` (
  `post_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `topic_id` bigint(11) NOT NULL,
  `forum_id` bigint(11) NOT NULL,
  `user_id` bigint(11) NOT NULL,
  `deleted_id` bigint(11) DEFAULT NULL,
  `report_id` bigint(11) DEFAULT NULL,
  `post_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `post_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `post_edited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_edited` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_posts_likes
--

CREATE TABLE IF NOT EXISTS `phpcore_posts_likes` (
  `post_id` bigint(11) NOT NULL,
  `user_id` bigint(11) NOT NULL,
  `like_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_profile_posts
--

CREATE TABLE IF NOT EXISTS `phpcore_profile_posts` (
  `profile_post_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(11) NOT NULL,
  `deleted_id` bigint(11) DEFAULT NULL,
  `report_id` bigint(11) DEFAULT NULL,
  `profile_id` bigint(11) NOT NULL,
  `profile_post_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `profile_post_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`profile_post_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_profile_posts_comments
--

CREATE TABLE IF NOT EXISTS `phpcore_profile_posts_comments` (
  `profile_post_comment_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `profile_post_id` bigint(11) NOT NULL,
  `user_id` bigint(11) NOT NULL,
  `deleted_id` bigint(11) DEFAULT NULL,
  `report_id` bigint(11) DEFAULT NULL,
  `profile_id` bigint(11) NOT NULL,
  `profile_post_comment_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `profile_post_comment_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`profile_post_comment_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_reports
--

CREATE TABLE IF NOT EXISTS `phpcore_reports` (
  `report_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `report_type` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `report_type_id` bigint(11) NOT NULL,
  `report_type_user_id` bigint(11) NOT NULL,
  `report_status` int(11) NOT NULL DEFAULT '0',
  `report_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`report_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_reports_reasons
--

CREATE TABLE IF NOT EXISTS `phpcore_reports_reasons` (
 `report_reason_id` bigint(11) NOT NULL AUTO_INCREMENT,
 `report_id` bigint(11) NOT NULL,
 `user_id` bigint(11) NOT NULL,
 `report_reason_text` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
 `report_reason_type` tinyint(1) NOT NULL DEFAULT '0',
 `report_reason_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`report_reason_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


-- --------------------------------------------------------

--
-- phpcore_topics
--

CREATE TABLE IF NOT EXISTS `phpcore_topics` (
  `topic_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `forum_id` bigint(11) NOT NULL,
  `category_id` bigint(11) NOT NULL,
  `user_id` bigint(11) NOT NULL,
  `deleted_id` bigint(11) DEFAULT NULL,
  `report_id` bigint(11) DEFAULT NULL,
  `topic_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `topic_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `topic_url` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_image` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_views` int(11) NOT NULL DEFAULT '0',
  `topic_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `topic_posts` mediumint(11) NOT NULL DEFAULT '0',
  `topic_edited` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_edited` tinyint(4) NOT NULL DEFAULT '0',
  `is_sticky` tinyint(1) NOT NULL DEFAULT '0',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `phpcore_topics` (`user_id`, `forum_id`, `category_id`, `topic_name`, `topic_text`, `topic_url`) VALUES
(1, 1, 1, 'První téma', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Pellentesque pretium lectus id turpis. Etiam commodo dui eget wisi. Vestibulum erat nulla, ullamcorper nec, rutrum non, nonummy ac, erat. Curabitur sagittis hendrerit ante. Proin mattis lacinia justo. Phasellus et lorem id felis nonummy placerat.', 'prvni-tema');

-- --------------------------------------------------------

--
-- phpcore_topics_labels
--

CREATE TABLE IF NOT EXISTS `phpcore_topics_labels` (
  `topic_id` bigint(11) NOT NULL,
  `label_id` bigint(11) NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_topics_likes
--

CREATE TABLE IF NOT EXISTS `phpcore_topics_likes` (
  `topic_id` bigint(11) NOT NULL,
  `user_id` bigint(11) NOT NULL,
  `like_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_users
--

CREATE TABLE IF NOT EXISTS `phpcore_users` (
  `user_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `group_id` bigint(11) NOT NULL,
  `user_name` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_hash` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_password` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_email` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_text` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `user_profile_image` varchar(20) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_header_image` varchar(20) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_last_activity` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_signature` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `user_location` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `user_age` int(11) NOT NULL DEFAULT '0',
  `user_gender` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT 'undefined',
  `user_posts` mediumint(11) NOT NULL DEFAULT '0',
  `user_topics` mediumint(11) NOT NULL DEFAULT '0',
  `user_reputation` mediumint(11) NOT NULL DEFAULT '0',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- phpcore_users_notifications
--

CREATE TABLE IF NOT EXISTS `phpcore_users_notifications` (
  `user_notification_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `to_user_id` bigint(11) NOT NULL,
  `user_id` bigint(11) NOT NULL,
  `user_notification_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_notification_type_id` int(11) NOT NULL,
  `user_notification_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_notification_id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- phpcore_users_unread
--

CREATE TABLE IF NOT EXISTS `phpcore_users_unread` (
  `conversation_id` bigint(11) NOT NULL,
  `user_id` bigint(11) NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------


--
-- phpcore_verify_account
--

CREATE TABLE IF NOT EXISTS `phpcore_verify_account` (
  `user_id` bigint(11) NOT NULL,
  `account_code` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------


--
-- phpcore_verify_email
--

CREATE TABLE IF NOT EXISTS `phpcore_verify_email` (
  `user_id` bigint(11) NOT NULL,
  `email_code` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_email` varchar(225) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
