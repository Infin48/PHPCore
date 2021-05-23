<?php

/**
 * This file is part of the PHPCore forum software
 * 
 * Made by InfinCZ 
 * @link https://github.com/Infin48
 * 
 * Translated by alchemMX
 * @link https://github.com/alchemMX
 * 
 * @copyright (c) PHPCore Limited https://phpcore.cz
 * @license GNU General Public License, version 3 (GPL-3.0)
 */

$language = [];

foreach (glob(ROOT. '/Languages/en/Admin/*.php') as $path) {
    if (basename($path)!= 'Load.language.php') {
        require $path;
    }
}

$language = array_merge($language, [

    'L_BY' => 'from',

    'L_ICON' => 'Icon',
    'L_ICON_LIST' => 'Icon list',
    'L_ICON_LIST_DESC' => 'Clicking on the button will open the FontAwesome page in a new window',
    'L_ICON_NAME' => 'Icon name',
    'L_ICON_STYLE' => 'Icon style',

    'L_FAS' => 'fas',
    'L_FAR' => 'far',
    'L_FAB' => 'fab',

    'L_INFO' => 'Information',
    'L_EDIT' => 'Edit',

    'L_SEND' => 'Send',

    'L_HOST' => 'Server',
    'L_PORT' => 'Port',

    'L_DELETED_USER' => 'Deleted user',

    'L_OPTIONS' => 'Options',

    'L_RECORD' => 'Record',

    'L_UPDATE_ALERT' => 'Update',

    'L_TODAY' => 'Today',
    'L_AT' => 'at',
    'L_TOMORROW' => 'Yesterday',
    'L_BACK' => 'Back',

    'L_REGISTERED' => 'Registered',

    'L_NO' => 'No',
    'L_YES' => 'Yes',

    'L_LINK' => 'Link',

    'L_DETAILS' => 'Details',

    'L_REMOVE' => 'Delete',
    'L_INTERNAL_ERROR' => 'An internal error was found',

    'L_CONTENT_TYPE' => 'Content type',
    'L_CONTENT_LIST' => [
        'Topic' => 'Topic',
        'Post' => 'Post',
        'ProfilePost' => 'Profile post',
        'ProfilePostComment' => 'Profile comment'
    ],

    'L_RECORD_ID' => 'Record ID',

    'L_ONLINE' => 'Online',

    'L_DELETE' => 'Delete',
    'L_SHOW' => 'View',

    'L_SUBMIT' => 'Submit',

    'L_TOPIC_NAME' => 'Topic name',
    'L_AUTHOR' => 'Content author',

    'L_NAME' => 'Name',
    'L_TEXT' => 'Text',
    'L_EMAIL' => 'Email',
    'L_USERNAME' => 'Username',
    'L_DESCRIPTION' => 'Description',

    'L_PASSWORD' => 'Password',

    'L_PASSWORD_NEW' => 'New password',
    'L_PASSWORD_DESC' => 'Password must contain at least 6 characters',

    'L_TOPIC_ID' => 'Topic ID',
    'L_TOPIC_ID_DESC' => 'ID under which the topic is stored in the database',
    'L_POST_ID' => 'Post ID',
    'L_POST_ID_DESC' => 'ID under which the post is stored in the database',
    'L_PROFILE_POST_ID' => 'Profile post ID',
    'L_PROFILE_POST_ID_DESC' => 'ID under which the profile post is stored in the database',
    'L_PROFILE_POST_COMMENT_ID' => 'Profile comment ID',
    'L_PROFILE_POST_COMMENT_ID_DESC' => 'ID under which the profile comment is stored in the database',

    'L_POST_TOPIC_NAME' => 'Topic',
    'L_TOPIC_NAME' => 'Topic name',

    'L_ALERT' => 'Security information',

    'L_MOVE_UP' => 'Move up',
    'L_MOVE_DOWN' => 'Scroll down',
    'L_MAIN_ADMIN' => 'General manager',
    'L_EXTERNAL_LINK' => 'External link',

    'L_KEY' => 'Key',
    'L_SHOW_MORE' => 'Show more',
    'L_CREATED_BY' => 'Created by',

    'L_TAB_POST' => 'Posts',
    'L_TAB_TOPIC' => 'Topics',
    'L_TAB_PROFILEPOST' => 'Profile posts',
    'L_TAB_ADMIN' => 'Admin panel',
]);
