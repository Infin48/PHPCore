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

foreach (glob (ROOT. '/Languages/en/*.php') as $path) {
    if (basename ($path)!= 'Load.language.php') {
        require $path;
    }
}

$language = array_merge($language, [
    'L_DOMAIN' => $_SERVER['SERVER_NAME'],
    'L_ERROR' => 'Error 404',
    'L_ERROR_DESC' => 'We\'re sorry, but the page you requested could not be found or you do not have the appropriate permissions to access this page',
    'L_TITLE_PAGE' => 'Front page',
    'L_BY' => 'By',

    'L_FORUM' => 'Forum',

    'L_TERMS' => 'Terms and conditions',

    'L_DETAILS' => 'Details',
    'L_REASON' => 'Reason',

    'L_COOKIE_BUTTON' => 'I understand',

    'L_CANCEL' => 'Cancel',

    'L_RE' => 'Re',

    'L_DELETED_USER' => 'Deleted user',

    'L_AUTHOR' => 'Content author',

    'L_HIDE' => 'Hide',

    'L_CONTENT_DELETED' => 'This content has been deleted',

    'L_BACK' => 'Back',

    'L_EDITED' => 'Managed',
    'L_CHANGE' => 'Change',

    'L_YOU' => 'you',

    'L_MAX_IMAGE_SIZE' => 'Maximum image size is {size} KB',

    'L_FOUND_ERROR' => 'An error occurred',

    'L_SHOW' => 'View',

    'L_ONLINE' => 'Online',

    'L_INTERNAL_ERROR' => 'An internal error was found',

    'L_LINK' => 'Link',

    'L_MESSAGES' => 'Messages',
    'L_STATISTICS' => 'Statistics',
    'L_REGISTERED' => 'Registered',

    'L_PAGE' => 'Page',
    'L_PAGE_OF' => 'z',

    'L_MENU' => 'Menu',
    'L_CREATED' => 'Created',

    'L_WROTE' => 'wrote',
    'L_QUOTE' => 'Quote',
    'L_REPORT' => 'Report',

    'L_SEND' => 'Submit',

    'L_AT' => 'at',
    'L_TODAY' => 'Today',
    'L_TOMORROW' => 'Yesterday',
]);
