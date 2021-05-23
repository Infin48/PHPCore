<?php

/**
 * This file is part of the PHPCore forum software
 * 
 * Made by InfinCZ 
 * @link https://github.com/Infin48
 *
 * @copyright (c) PHPCore Limited https://phpcore.cz
 * @license GNU General Public License, version 3 (GPL-3.0)
 */

define('TABLE_BUTTONS', 'phpcore_buttons b');
define('TABLE_BUTTONS_SUB', 'phpcore_buttons_sub bs');
define('TABLE_CATEGORIES', 'phpcore_categories c');
define('TABLE_CATEGORIES_PERMISSION_SEE', 'phpcore_categories_permission_see cps');
define('TABLE_DELETED_CONTENT', 'phpcore_deleted_content dc');
define('TABLE_FORGOT', 'phpcore_forgot_password fp');
define('TABLE_FORUMS', 'phpcore_forums f');
define('TABLE_FORUM_ICONS', 'phpcore_forums_icons fi');
define('TABLE_FORUMS_PERMISSION_POST', 'phpcore_forums_permission_post fpp');
define('TABLE_FORUMS_PERMISSION_SEE', 'phpcore_forums_permission_see fps');
define('TABLE_FORUMS_PERMISSION_TOPIC', 'phpcore_forums_permission_topic fpt');
define('TABLE_GROUPS', 'phpcore_groups g');
define('TABLE_LABELS', 'phpcore_labels l');
define('TABLE_LOG', 'phpcore_logs lg');
define('TABLE_NOTIFICATIONS', 'phpcore_notifications n');
define('TABLE_PAGES', 'phpcore_pages pg');
define('TABLE_PERMISSIONS', 'phpcore_permissions pi');
define('TABLE_POSTS', 'phpcore_posts p');
define('TABLE_POSTS_LIKES', 'phpcore_posts_likes pl');
define('TABLE_CONVERSATIONS', 'phpcore_conversations c');
define('TABLE_CONVERSATIONS_MESSAGES', 'phpcore_conversations_messages cm');
define('TABLE_CONVERSATIONS_RECIPIENTS', 'phpcore_conversations_recipients cr');
define('TABLE_PROFILE_POSTS', 'phpcore_profile_posts pp');
define('TABLE_PROFILE_POSTS_REPORTS', 'phpcore_profile_post_reports ppr');
define('TABLE_PROFILE_POSTS_COMMENTS', 'phpcore_profile_posts_comments ppc');
define('TABLE_REPORTS', 'phpcore_reports r');
define('TABLE_REPORTS_REASONS', 'phpcore_reports_reasons rr');
define('TABLE_SETTINGS', 'phpcore_settings s');
define('TABLE_TOPICS', 'phpcore_topics t');
define('TABLE_TOPICS_DELETED', 'phpcore_topics_deleted td');
define('TABLE_TOPICS_LIKES', 'phpcore_topics_likes tl');
define('TABLE_TOPICS_LABELS', 'phpcore_topics_labels tlb');
define('TABLE_USERS', 'phpcore_users u');
define('TABLE_USERS_NOTIFICATIONS', 'phpcore_users_notifications un');
define('TABLE_USERS_UNREAD', 'phpcore_users_unread unr');
define('TABLE_VERIFY_ACCOUNT', 'phpcore_verify_account va');
define('TABLE_VERIFY_EMAIL', 'phpcore_verify_email ve');

define('RAND', mt_rand());
define('DATE', 'j M Y, G:i');
define('DATE_DATABASE', date('Y-m-d H:i:s'));
define('SESSION_ID', session_id());

define('PLUS', 'd998db-d_&f');
define('MINUS', '88t-Dqq_@');

define('SINGLE', (int)28);
define('ROWS', (int)66);

define('int', 'int');
define('string', 'string');

define ('NOTIFICATION', [
    1 => 'info',
    2 => 'notification',
    3 => 'warning'
]);

define('PROFILE_IMAGES_COLORS', [
    'red', 'blue', 'purple', 'grey', 'brown', 'green', 'orange'
]);

define('MAX_NEWS', 10);
define('MAX_USERS', 20);
define('MAX_POSTS', 10);
define('MAX_TOPICS', 10);
define('MAX_MESSAGES', 10);
define('MAX_PROFILE_POSTS', 10);
define('MAX_PRIVATE_MESSAGES', 20);

define('MAX_REPORTED_POST', 20);
define('MAX_REPORTED_TOPIC', 20);
define('MAX_REPORTED_COMMENTS', 20);
define('MAX_REPORTED_PROFILE_POSTS', 20);

define('REQUIRE_LOGIN', 1152);
define('REQUIRE_LOGOUT', 3489);
define('OPTIONAL_LOGIN', 5927);

define('EDITOR_BIG', 'Big');
define('EDITOR_SMALL', 'Small');
define('EDITOR_MEDIUM', 'Medium');

define('CONTEXT', stream_context_create(['http' => ['method' => 'GET','header' => ['User-Agent: PHP']]]));

define('SUCCESS_SESSION', 'SUCCESS_SESSION');
define('SUCCESS_RETURN', 'SUCCESS_RETURN');